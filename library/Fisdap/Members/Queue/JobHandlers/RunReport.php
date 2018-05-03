<?php namespace Fisdap\Members\Queue\JobHandlers;

use Fisdap\Entity\Message;
use Fisdap\Entity\Report;
use Fisdap\Entity\ReportConfiguration;
use Fisdap_Reports_Report;
use Illuminate\Contracts\Queue\Job;


/**
 * Class RunReport
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  jmortenson
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class RunReport extends JobHandler
{
    /**
     * @var integer Lifetime for the report results to be saved
     * This is either a number of seconds if less than 30 days, or a unix timestamp if > 30 days
     */
    private $resultsLifetime = 0; // 0 == indefinite

    /**
     * @var Report The sub-class representing the Report type
     */
    private $reportEntity;

    /**
     * @var ReportConfiguration The report configuration we are running
     */
    private $reportConfig;

    /**
     * @var int Unix timestamp for when the report processing started
     */
    private $timeStarted;

    /**
     * If a report runs longer than the threshold, notification should occur
     * @var int Notification threshold in minutes
     */
    private $notificationThreshold = 2;


    /**
     * @inheritdoc
     */
    public function fire(Job $job, $data)
    {
        $this->logStart($job);

        // make job available to other methods
        $this->job = $job;

        // make sure we have database connections
        $this->reopenDBConnections();

        // allow MySQL connections to last longer
        $mysqlTimeout = 3600;
        $this->em->getConnection()->exec("SET SESSION wait_timeout = {$mysqlTimeout}");

        $this->db->query("SET SESSION wait_timeout = {$mysqlTimeout}");

        // Set the lifetime for storing results data
        if (isset($data['lifetime'])) {
            $this->resultsLifetime = $data['lifetime'];
        } else {
            $this->resultsLifetime = 7 * 24 * 60 * 60; // 7 days
        }

        // load the report configuration
        $this->reportConfig = \Fisdap\EntityUtils::getEntity('ReportConfiguration', $data['configurationId']);
        if ( ! $this->reportConfig instanceof ReportConfiguration) {
            // can't run this job without the config loading properly, so report
            $this->log($job, 'Could not load report configuration entity', 'error');

            // wait a couple seconds then release the job. Might be caused by a race condition (waiting on Doctrine flush)
            // job will just be re-tried (up to value of '--tries' option on listener)
            sleep(2);
            $this->job->release();

            return;
        }

        // masquerade as the user who requested that this config be run
        if (\Fisdap_Auth_Adapter_Db::masquerade($this->reportConfig->user_context->user)) {
            // success?
        } else {
            throw new \Exception(
                'Failed to masquerade for user id ' . $this->reportConfig->user_context->user->id . ' for report generation'
            );
        }

        // construct the Report object
        $this->reportEntity = $this->reportConfig->report;
        $reportClass = 'Fisdap_Reports_' . $this->reportEntity->class;

        // DEBUG
        $startMem = round(memory_get_usage() / 1024 / 1024);
        $this->log($job, 'Report worker for ' . $this->reportEntity->class . ' started at: ' . $startMem . 'M', 'debug');
        // record when processing started
        $this->timeStarted = time();

        // Attempt to run the report
        /** @var Fisdap_Reports_Report $report */
        $report = new $reportClass($this->reportEntity, $this->reportConfig);
        $report->runReport();

        // cache the results of the report
        // assuming we have automatic_serialization turned on

        // DEBUG
        //$this->logger->debug('$report->data serialized length: ' . round(strlen(serialize($report->data)) / 1024 / 1024) . 'M');
        //$this->logger->debug('$report->header serialized length: ' . round(strlen(serialize($report->header)) / 1024 / 1024) . 'M');
        $this->cacheReportParts($report);

        // Notify, if needed
        $processingTime = time() - $this->timeStarted;
        if ($processingTime > ($this->notificationThreshold * 60)) {
            $this->notifyReportFinished();
        }

        // DEBUG
        $endMem = round(memory_get_usage() / 1024 / 1024);
        $peakMem = round(memory_get_peak_usage() / 1024 / 1024);
        $this->log(
            $job,
            'Report worker for ' . $this->reportEntity->class . ' ENDED at: ' . $endMem . 'M, PEAK is: ' . $peakMem . 'M',
            'debug'
        );

        $this->logSuccess($job);

        $this->job->delete();

        // clean up
        unset($config, $report);
        $this->job = $this->reportEntity = $this->reportConfig = NULL;
    }


    /**
     * Save report results (including data, header and footer) to a series of documents in the cache
     * including a master document that stores the map of cache keys that, together, comprise the report results
     * We're splitting this into multiple documents to mitigate risk of hitting cache document size limits
     * which in Couchbase is 20MB, for example
     *
     * @param Fisdap_Reports_Report $report The report, with data already calculated (ie $report->runReport() has been run)
     */
    private function cacheReportParts(Fisdap_Reports_Report $report) {
        // save each data document within the $report->data
        $cachePrefix = 'reports_result_for_config_' . $this->reportConfig->id;
        $dataCacheIds = array();
        $i = 0;
        if (is_array($report->data) && !empty($report->data)) {
            foreach ($report->data as $key => $data) {
                $cacheId = $cachePrefix . '_' . $i;
                $dataCacheIds[] = $cacheId;
                $this->cache->save(
                    array(
                        'key' => $key,
                        'data' => $data,
                    ), $cacheId, [], $this->resultsLifetime);
                $i++;
            }
        } else {
            // huh? No tables
            $this->log(
                $this->job, 'Found no data tables to cache. $report->data is: ' . print_r($report->data, TRUE), 'debug'
            );
        }
        // save header and footer
        $headerCacheId = $cachePrefix . '_header';
        $this->cache->save(
            array(
                'key' => 'header',
                'data' => $report->header,
            ), $headerCacheId, [], $this->resultsLifetime);
        $footerCacheId = $cachePrefix . '_footer';
        $this->cache->save(
            array(
                'key' => 'footer',
                'data' => $report->footer,
            ), $footerCacheId, [], $this->resultsLifetime);

        // Save the master document
        $this->cache->save(
            array(
                'dataKeys' => $dataCacheIds,
                'headerKey' => $headerCacheId,
                'footerKey' => $footerCacheId,
            ), $cachePrefix, [], $this->resultsLifetime);
    }


    private function notifyReportFinished() {
        // the user: $this->reportConfig->user_context->user
        $path = '/reports/index/display/report/TestItemAnalysis/config/' . $this->reportConfig->id;
        $subject = 'Your ' . $this->reportEntity->name . ' report is ready';

        $url = \Zend_Registry::get('host') . $path;

        $body = '<a href="' . $url . '">View your report results</a>.';
        $plaintext = 'View your report results: ' . $url;

        // Send a Fisdap message
        $message = new Message();
        $message->set_title($subject);
        $message->set_body($body);
        $message->set_author_type(1);
        $message->deliver(array($this->reportConfig->user_context->user));

        // Send an email
        $mail = new \Fisdap_TemplateMailer();
        $mail->setSubject($subject);
        $mail->setBodyText($plaintext);
        $mail->setBodyHtml($body);
        $mail->addTo($this->reportConfig->user_context->user->email);
        $mail->send();
    }
} 