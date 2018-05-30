<?php

class MyFisdap_EventController extends Fisdap_Controller_Private
{
    public function init()
    {
        $this->loggedInUser =  \Fisdap\Entity\User::getLoggedInUser();
    }
    
    /**
     * This is just a test function right now
     */
    public function indexAction()
    {
        $this->view->output = '<a href="/my-fisdap/event/create">Create an event</a>';
    }

    /**
     * This is just a test function right now
     */
    public function createAction()
    {
        $eventsOutput = '';
        
        /*
        $event = new \Fisdap\Entity\Event();

        $timeStart = new \DateTime('now');
        $event->set_start($timeStart);

        $timeEnd = new \DateTime('now');
        $timeEnd->add(new DateInterval('PT3H'));
        $event->set_end($timeEnd);

        $event->save();


        //$this->view->output = 'Message title: ' . $message->title;
        $eventsOutput = 'event resulting: ';// . $event->format();
         */
        
        $eventsOutput .= '<p>My timezone offset: ' . $this->loggedInUser->getCurrentRoleData()->program->program_settings->timezone->standard_offset . '</p>';
        
        // load some events
        $repo = $event->getEntityRepository();
        $all = $repo->findAll();
        $eventsOutput .= '<table><tr><td>Formatted</td><td>Orig timezone</td><td>Orig offset</td><td>Orig time</td></tr>';
        foreach ($all as $e) {
            if ($e->event_id > 0) {
                $eventsOutput .= '<tr><td>' . $e->format() . '</td>';
                $eventsOutput .= '<td>' . $e->timezone->name . '</td><td>' . $e->timezone->standard_offset . '</td>';
                $eventsOutput .= '<td>' . $e->format('g:ia', 'm/d/y', false, true, true) . '</td>';
                $eventsOutput .= '</tr>';
            }
        }
        $eventsOutput .= '</table>';
        
        $this->view->output = $eventsOutput;
    }
}
