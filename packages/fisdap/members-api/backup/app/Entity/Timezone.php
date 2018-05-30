<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Timezones
 *
 * @Entity(repositoryClass="Fisdap\Data\Timezone\DoctrineTimezoneRepository")
 * @Table(name="fisdap2_timezone")
 * @HasLifecycleCallbacks
 */
class Timezone extends Enumerated
{
    /**
     * @OneToMany(targetEntity="ProgramSettings", mappedBy="timezone")
     */
    protected $programSettings;
    
    /**
     * @Column(type="integer")
     */
    protected $standard_offset;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_dst;
    
    /**
     * @Column(type="string")
     */
    protected $mysql_offset;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $icalendar_name;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $icalendar_definition;
    
    /**
     * Getters
     */
    public function get_standard_offset()
    {
        return $this->standard_offset;
    }
    
    
    /**
     * Overriding the getFormOptions here.  Need to input the GMT offset in
     * the name string.  Everything else stays the same.
     *
     * @param Boolean $na Determines whether or not to include an "N/A" option
     * in the list. Defaults to false.
     * @param Boolean $sort Determines whether or not to sort the returning
     * list. Defaults to true.
     *
     * @return Array containing the requested list of entities, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort=true, $displayName = "name")
    {
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findAll();
        
        $options = array();
        
        foreach ($results as $result) {
            $options[$result->id] = $result->name . " (" . $result->standard_offset . " GMT)";
        }
        
        if ($sort) {
            asort($options);
        }
        
        if ($na) {
            $options[0] = "N/A";
        }
        
        return $options;
    }
    
    
    /**
     * Return DateTime that is converted into the proper timezone.
     * Works with Fisdap Timezone offsets. Can handle DST if Timezone entities are provided
     *
     * @param DateTime $time The DateTime to be converted
     * @param mixed $incomingTimezone The Fisdap Timezone (or integer offset) of the incoming date
     * @param mixed $targetTimezone The Fisdap Timezone (or integer offset) to which the DateTime should be converted
     *
     * @return DateTime A DateTime that is properly converted to the target Timezone
     */
    public static function getLocalDateTime($time, $incomingTimezone, $targetTimezone)
    {
        // get offsets
        if ($incomingTimezone instanceof Timezone) {
            $incomingOffset = $incomingTimezone->get_standard_offset();
        } elseif (is_numeric($incomingTimezone)) {
            $incomingOffset = $incomingTimezone;
        } else {
            return false;
        }
        if ($targetTimezone instanceof Timezone) {
            $targetOffset = $targetTimezone->get_standard_offset();
        } elseif (is_numeric($targetTimezone)) {
            $targetOffset = $targetTimezone;
        } else {
            return false;
        }
        
        // deal with daylight savings time differences, if Timezone objects were supplied
        if (($incomingTimezone instanceof Timezone) && ($targetTimezone instanceof Timezone)) {
            // if the two timezones both use DST, we shouldn't have to convert anything
            // but if they don't agree about DST, and the server says DST is active, we need to convert
            if ($incomingTimezone->is_dst != $targetTimezone->is_dst && date('I')) {
                if ($targetTimezone->is_dst) {
                    // the target is DST, incoming time is not. Add an hour.
                    $time->add(new \DateInterval('PT1H'));
                } else {
                    // the target is NOT DST, incoming time IS DST. Subtract an hour.
                    $time->sub(new \DateInterval('PT1H'));
                }
            }
        }
        
        // Now convert using the standard offsets
        $amount = abs($incomingOffset - $targetOffset);
        if ($incomingOffset > $targetOffset) {
            // we need to subtract time
            $time->sub(new \DateInterval('PT' . $amount . 'H'));
        } else {
            // we need to add time
            $time->add(new \DateInterval('PT' . $amount . 'H'));
        }
        
        return $time;
    }
    
    /**
     * This function takes a local timestamp (from the server) and converts it
     * into a timestamp localized for a user in a potentially different timezone.
     *
     * @param integer $timestamp Server-localized timestamp.
     * @return integer Timestamp that represents the users local time.
     */
    public function getLocalServerTime($timestamp=null)
    {
        if ($timestamp == null) {
            $timestamp = time();
        }
        
        // Subtract out the servers timezone offset (in seconds)
        $timestamp -= date("Z");
        
        // $timestamp should now be a GMT (+-0) timestamp...
        // Add on the correct amount for the users timezone...
        $timestamp += $this->standard_offset * 60 * 60;
        
        // If the selected timezone is set to use DST, offset by it.
        if ($this->is_dst) {
            // If we're currently in DST, this will be the number of seconds in
            // an hour.  Otherwise, it'll be 0 and won't affect the returned
            // time.
            $timestamp += date('I') * 60 * 60;
        }
        
        return $timestamp;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'standardOffset' => $this->get_standard_offset(),
            'isDST' => $this->is_dst
        ];
    }
}
