<?php

/**
 * View Helper to display a table of requirement attachments for a give user
 */
use Fisdap\Data\Event\EventLegacyRepository;
use Fisdap\Data\Requirement\RequirementRepository;


/**
 * @package Portfolio
 */
class Portfolio_View_Helper_UserComplianceAccordion extends Zend_View_Helper_Abstract 
{
	/**
	 * @var RequirementRepository
	 */
	private $requirementRepository;

	/**
	 * @var EventLegacyRepository
	 */
	private $eventLegacyRepository;

	public $view;

	protected $_html;


	/**
	 * Portfolio_View_Helper_UserComplianceAccordion constructor.
	 *
	 * @param RequirementRepository $requirementRepository
	 * @param EventLegacyRepository $eventLegacyRepository
	 * @param null                  $view
	 */
    public function __construct(
			RequirementRepository $requirementRepository,
			EventLegacyRepository $eventLegacyRepository,
			$view = null
	) {
		$this->requirementRepository = $requirementRepository;
		$this->eventLegacyRepository = $eventLegacyRepository;

		if ($view) {
			$this->view = $view;
		}
	}
	
	public function userComplianceAccordion($user_context, $filterBy, $req_associations)
	{
		$date_format = "M j, Y";
		$program_name = $user_context->program->name;
		$activeOnly = true;
		
		$attachments = \Fisdap\EntityUtils::getRepository("Requirement")->getAttachments($user_context->id, $filterBy, $activeOnly);
		
		$this->_html = "";
		
		if (count($attachments) < 1) {
			switch ($filterBy) {
				case "all-reqs":
					$status = " has no assigned requirements.";
					break;
				case "pending":
					$status = " has no requirements in progress.";
					break;
				case "compliant-only":
					$status = " has no compliants for any assigned requirements.";
					break;
				case "non-compliant-only":
					$status = " has no non-compliant requirements.";
					break;
			}
			$this->_html .= "<div class='none-found'>".$user_context->user->getName() . $status."</div>";
		} else {
			// loop through all the attachments		
			foreach ($attachments as $attachment) {
				if (!$req_associations[$attachment->requirement->id]) {
					// if this is a req with no associations, they must all be inactive
					continue;
				}
				
				$status_class = $status = $attachment->getStatus();
				switch ($status) {
					case "in progress":
						$date = "due: ".$attachment->due_date->format($date_format);
						$status_class = "pending";
						break;
					case "compliant":
						$date = $attachment->expiration_date ? "exp: ".$attachment->getExpirationDate($date_format) : "";
						break;
					case "non-compliant":
						if ($attachment->completed) {
							$date = "expired " . $attachment->getExpirationDate($date_format);
						} else {
							$date = "past due ";
							if ($attachment->due_date) {
								$date .= $attachment->due_date->format($date_format);
							}
						}
						break;
				}
				$this->_html .=
					"<div class='accordionHeader $status_class'>
						<table class='attachment-row'>
							<tr>
								<td class='req_col'>
									<div class='imgWrapper'><img src='/images/accordion_arrow_right.png'></div>".
									$attachment->requirement->name.
								"</td>
								<td class='status_col'>$status</td>
								<td class='expiration_col'>$date</td>
							</tr>
						</table>
					</div>
					
					<div class='accordionContent'>
						<div class='history-btn-wrapper extra-small'>
							<a href='#' class='history-btn extra-small' data-attachmentid='" . $attachment->id . "'>History</a>
						</div>
						
						<div class='table-label'>Required by:</div>";
				
				// if this req is associated with the program	
				if ($req_associations[$attachment->requirement->id]['program']) {
					$this->_html .= "<table class='attachment-row'>
											<tr>
												<td class='req_col program_req' colspan=3>
													<img src='/images/icons/program-requirement.png' class='icon'>
													<span class='req-location'>$program_name</span>
												</td>
											</tr>";
				}

				// if this req is associated with a site
				if ($req_associations[$attachment->requirement->id]['site']) {
					$upcoming_events = $this->eventLegacyRepository->getUpcomingEventsByUserContextId($user_context->id);
					$this->_html .= "<table class='attachment-row'>";
					foreach ($req_associations[$attachment->requirement->id]['site'] as $type => $sites) {
						// add upcoming event info
						foreach ($sites as $site_id => $site) {
							$site['upcoming_event_id'] = $upcoming_events[$site_id]['event_id'];
							if ($upcoming_events[$site_id]['event_id']) {
								$site['upcoming_event_date'] = date($date_format, strtotime($upcoming_events[$site_id]['start_datetime']));
							}
							$sites[$site_id] = $site;
						}
						$this->_html .= $this->view->partialLoop("requirementSiteAssociationRow.phtml", $sites);
					}
				}
				
				
				$this->_html .= "</table>
					</div>
					<div class='clear'></div>";
			}
		}
		$this->_html .= "<div id='history-modal-container'>hello world</div>";
		return $this->_html;
	}
}
