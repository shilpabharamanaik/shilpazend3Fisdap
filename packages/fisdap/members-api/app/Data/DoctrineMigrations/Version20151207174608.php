<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Removing curly quotes from product descriptions
 */
class Version20151207174608 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Skills tracker - limited
        $STLdescription = "Define custom skill sheets and goals for your program. Allow your students to document ".
            "skills and patient care online, and track their progress and competency with real-time reports. ".
            "Skills Tracker (Limited) allows for 10 Field, 10 Clinical, and 40 Lab shifts per student.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STLdescription.'" WHERE id = 10 LIMIT 1');

        // Skills tracker - unlimited
        $STUdescription = "Define custom skill sheets and goals for your program. Allow your students to document ".
            "skills and patient care online, and track their progress and competency with real-time reports. ".
            "Skills Tracker (Unlimited) allows for an unlimited number of shifts per student.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STUdescription.'" WHERE id = 1 LIMIT 1');

        // Skills tracker - alt professions
        $STAdescription = "Define custom skill sheets and goals for your program. Allow your students to document ".
            "skills and patient care online, and track their progress and competency with real-time reports.";
        $STAproducts = "16, 18, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 43, 45, 48, 50, 52, 55";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STAdescription.'" WHERE id IN ('.$STAproducts.') LIMIT 18');

        // Scheduler - Limited
        $SchLdescription = "Schedule students' internships on a live calendar where educators, clinicians, preceptors, ".
            "and students have the ability to view and interact with shifts. Set requirements for immunizations, ".
            "certifications, trainings, and more, and track student compliance on a shift-by-shift basis. Scheduler ".
            "(Limited) allows for 10 Field, 10 Clinical, and 40 Lab shifts per student.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchLdescription.'" WHERE id = 11 LIMIT 1');

        // Scheduler - Unlimited
        $SchUdescription = "Schedule students' internships on a live calendar where educators, clinicians, preceptors, ".
            "and students have the ability to view and interact with shifts. Set requirements for immunizations, ".
            "certifications, trainings, and more, and track student compliance on a shift-by-shift basis. Scheduler ".
            "(Unlimited) allows for an unlimited number of shifts per student.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchUdescription.'" WHERE id = 2 LIMIT 1');

        // Nurse Refresher Scheduler (Limited)
        $NSchdescription = "Schedule Nurse Refresher students' clinical placements on a live calendar where educators, ".
            "hospital administrators, and students have the ability to view and interact with shifts.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$NSchdescription.'" WHERE id = 54 LIMIT 1');

        // Scheduler - alt professions
        $SchAdescription = "Schedule students' clinical placements on a live calendar where educators, hospital ".
            "administrators, and students have the ability to view and interact with shifts. Set requirements for ".
            "immunizations, certifications, trainings, and more, and track student compliance on a shift-by-shift basis.";
        $SchAproducts = "17, 19, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 44, 46, 49, 51, 53, 56";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchAdescription.'" WHERE id IN ('.$SchAproducts.') LIMIT 1');

        // Australian Exam
        $AUSdescription = "Deliver a summative final exam in a secure, proctored environment in order to assess ".
            "students' terminal competency.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$AUSdescription.'" WHERE id = 47 LIMIT 1');

        // Paramedic Comprehensive exams
        $PCEdescription = "Deliver 200-question summative exams in a secure, proctored environment to assess whether ".
            "paramedic students are prepared for success on the NREMT.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PCEdescription.'" WHERE id = 3 LIMIT 1');

        // EMT Comprehensive Exams
        $ECEdescription = "Deliver 200-question summative exams in a secure, proctored environment to assess whether ".
            "EMT students are prepared for success on the NREMT.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ECEdescription.'" WHERE id = 4 LIMIT 1');

        // Paramedic Unit Exams
        $PUEdescription = "Deliver six 75-question exams in a secure, proctored environment. Each exam covers ".
            "(respectively) Airway, Cardiology, Medical, OB/Peds, Operations, and Trauma units and can be delivered ".
            "in the order that works best for your Paramedic curriculum.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PUEdescription.'" WHERE id = 5 LIMIT 1');

        // EMT Unit Exams
        $EUEdescription = "Deliver six 75-question exams in a secure, proctored environment. Each exam covers ".
            "(respectively) Airway, Cardiology, Medical, OB/Peds, Operations, and Trauma units, and can be delivered ".
            "in the order that works best for your EMT curriculum.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$EUEdescription.'" WHERE id = 6 LIMIT 1');

        // Paramedic Entrance Exam
        $PEEdescription = "Deliver this valid exam in a secure, proctored environment to assess students' preparedness ".
            "for paramedic school. This exam tests students on Anatomy, Physiology, Math, Reading, and EMT-level ".
            "knowledge.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PEEdescription.'" WHERE id = 20 LIMIT 1');

        // Paramedic Study Tools
        $PSTdescription = "Equip your students for independent study with Study Tools. Their account includes a ".
            "200-question practice exam to prepare for the NREMT, practice quizzes, podcasts, and interactive ".
            "skill demonstrations.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PSTdescription.'" WHERE id = 7 LIMIT 1');

        // EMT Study Tools
        $ESTdescription = "Equip your students for independent study with Study Tools. Their account includes a ".
            "200-question practice exam to prepare for the NREMT, practice quizzes, podcasts, and interactive ".
            "skill demonstrations.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ESTdescription.'" WHERE id = 8 LIMIT 1');

        // Preceptor Training
        $PTdescription = "Train good clinicians to be excellent mentors with this CECBEMS-approved online course that ".
            "explores Adult Education Theory. Preceptor training can be completed in as little as 4 hours.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PTdescription.'" WHERE id = 9 LIMIT 1');

        // Pilot Testing
        $Pilotdescription = "Staff only option to create Pilot Testing accounts.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$Pilotdescription.'" WHERE id = 12 LIMIT 1');

        // Paramedic Transition Course
        $PTCdescription = "Transition to the National Education Standards at the Paramedic level with this ".
            "CECBEMS-approved online course, which you can submit for 22 hours of continuing ".
            "education upon completion.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PTCdescription.'" WHERE id = 13 LIMIT 1');

        // EMT Transition Course
        $ETCdescription = "Transition to the National Education Standards at the EMT level with this ".
            "CECBEMS-approved online course, which you can submit for 24 hours of continuing ".
            "education upon completion.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ETCdescription.'" WHERE id = 14 LIMIT 1');

        // AEMT Transition Course
        $ATCdescription = "Transition to the National Education Standards at the AEMT level with this ".
            "CECBEMS-approved online course, which you can submit for 24 hours of continuing ".
            "education upon completion.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ATCdescription.'" WHERE id = 15 LIMIT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // reset product names

        // Skills tracker - limited
        $STLdescription = "Online shift reports and evaluations where students document the patient care data for ".
            "their entire field and clinical internship. Skills Tracker includes access to reports, accreditation ".
            "assistance and the portfolio.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STLdescription.'" WHERE id = 10 LIMIT 1');

        // Skills tracker - unlimited
        $STUdescription = "Online shift reports and evaluations where students document the patient care data for ".
            "their entire field and clinical internship. Skills Tracker includes access to reports, accreditation ".
            "assistance and the portfolio.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STUdescription.'" WHERE id = 1 LIMIT 1');

        // Skills tracker - alt professions
        $STAdescription = "Online documenting for patient care worksheets with graduation and goals reports.";
        $STAproducts = "16, 18, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 43, 45, 48, 50, 52, 55";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$STAdescription.'" WHERE id IN ('.$STAproducts.') LIMIT 18');

        // Scheduler - Limited
        $SchLdescription = "Schedule the students' internship online where educators, clinicians, preceptors and ".
            "students can all view and interact with the live calendar.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchLdescription.'" WHERE id = 11 LIMIT 1');

        // Scheduler - Unlimited
        $SchUdescription = "Schedule the students' internship online where educators, clinicians, preceptors and ".
            "students can all view and interact with the live calendar.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchUdescription.'" WHERE id = 2 LIMIT 1');

        // Nurse Refresher Scheduler (Limited)
        $NSchdescription = "Schedule Nurse Refresher students' clinical placements online where educators, hospital ".
            "administrators, and students can all view and interact with the live calendar.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$NSchdescription.'" WHERE id = 54 LIMIT 1');

        // Scheduler - alt professions
        $SchAdescription = "Schedule the students' clinical placements online where educators, hospital administrators, ".
            "and students can all view and interact with the live calendar.";
        $SchAproducts = "17, 19, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 44, 46, 49, 51, 53, 56";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$SchAdescription.'" WHERE id IN ('.$SchAproducts.') LIMIT 18');

        // Australian Exam
        $AUSdescription = "Secure summative final exam to assess students' terminal competency. This exam must be ".
            "given in a secure, proctored environment.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$AUSdescription.'" WHERE id = 47 LIMIT 1');

        // Paramedic Comprehensive exams
        $PCEdescription = "Secure 200 question summative, final exams assess students' readiness to succeed on the ".
            "NREMT. These exams must be given in a secure, proctored environment.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PCEdescription.'" WHERE id = 3 LIMIT 1');

        // EMT Comprehensive Exams
        $ECEdescription = "Secure 200 question summative, final exams assess students' readiness to succeed on the ".
            "NREMT. These exams must be given in a secure, proctored environment.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ECEdescription.'" WHERE id = 4 LIMIT 1');

        // Paramedic Unit Exams
        $PUEdescription = "Six secure 75 question exams for the Airway, Cardiology, Medical, OB/Peds, Operations and ".
            "Trauma units. These exams must be given in a secure, proctored environment.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PUEdescription.'" WHERE id = 5 LIMIT 1');

        // EMT Unit Exams
        $EUEdescription = "Six secure 75 question exams for the Airway, Cardiology, Medical, OB/Peds, Operations and ".
            "Trauma units. These exams must be given in a secure, proctored environment.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$EUEdescription.'" WHERE id = 6 LIMIT 1');

        // Paramedic Entrance Exam
        $PEEdescription = "A valid, secure exam intended to assess students' preparedness for paramedic school. This ".
            "exam tests students on Anatomy, Physiology, Math, Reading and EMT-level knowledge.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PEEdescription.'" WHERE id = 20 LIMIT 1');

        // Paramedic Study Tools
        $PSTdescription = "Independent practice test account for students to prepare for the NREMT...or even the ".
            "Fisdap Comprehensive Exams. Study Tools also includes practice quizzes, podcast, and skill demonstrations.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PSTdescription.'" WHERE id = 7 LIMIT 1');

        // EMT Study Tools
        $ESTdescription = "Independent practice test account for students to prepare for the NREMT...or even the ".
            "Fisdap Comprehensive Exams. Study Tools also includes practice quizzes, podcast, and skill demonstrations.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ESTdescription.'" WHERE id = 8 LIMIT 1');

        // Preceptor Training
        $PTdescription = "Our CECBEMS-approved online course trains good clinicians to be excellent mentors. Preceptor ".
            "Training is an introduction to adult education theory and can be completed in as little as 4 hours.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PTdescription.'" WHERE id = 9 LIMIT 1');

        // Pilot Testing
        $Pilotdescription = "Staff only option to make Pilot Testing accounts.";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$Pilotdescription.'" WHERE id = 12 LIMIT 1');

        // Paramedic Transition Course
        $PTCdescription = "Paramedic to Paramedic";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$PTCdescription.'" WHERE id = 13 LIMIT 1');

        // EMT Transition Course
        $ETCdescription = "EMT-B to EMT";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ETCdescription.'" WHERE id = 14 LIMIT 1');

        // AEMT Transition Course
        $ATCdescription = "EMT-I to AEMT";
        $this->addSql('UPDATE fisdap2_product SET description = "'.$ATCdescription.'" WHERE id = 15 LIMIT 1');
    }
}
