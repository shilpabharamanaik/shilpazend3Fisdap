<?php
/**
 * Class to do stuff with evals
 * @package Fisdap
 */
class EvalsController extends Fisdap_Controller_Base
{
    /**
     * This action deletes an eval
     */
    public function deleteEvalAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formValues = $request->getPost();
            $evalId = $formValues['evalId'];
            $eval = \Fisdap\EntityUtils::getEntity('EvalSessionLegacy', $evalId);

            // if the eval doesn't exit, pretend like we just deleted it so it is removed from the table
            if (is_null($eval)) {
                $this->_helper->json(true);
            }

            // make sure this user can delete this eval
            if (!$eval->userCanEdit($this->userContext)) {
                $this->_helper->json("You do not have permission to delete this eval.");
            }

            // if we're still here, we've got an eval that the user is allowed to delete, so delete it!
            $eval->delete();
            $query1 = "DELETE FROM Eval_ItemSessions WHERE EvalSession_id = $evalId";
            $query2 = "DELETE FROM Eval_CriticalCriteriaSessions WHERE EvalSession_id = $evalId";
            $query3 = "DELETE FROM Eval_Comment_Session WHERE EvalSession_id = $evalId";
            $query4 = "DELETE FROM DataEvalLinks WHERE EvalSession_id = $evalId";

            // get the database connection
            $db = \Zend_Registry::get('db');
            $db->query($query1);
            $db->query($query2);
            $db->query($query3);
            $db->query($query4);
            $this->_helper->json(true);
        } else {
            // go home, you are drunk
            $this->_redirect("/");
        }
    }
}
