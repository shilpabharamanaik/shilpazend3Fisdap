<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150507132610 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        //move all 'retired' exams (active = 3) to 'inactive' (active = 2), excluding the Blue Exam.
        $this->addSql('Update MoodleTestData set Active = 2 where TestData_id in (1,3,5,11,12,13,14,16,17,18,19,22,24,25,26,27,28,29,30,31,32,33,35,37,63,65,66,68) limit 28');

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('Update MoodleTestData set Active = 3 where TestData_id in (1,3,5,11,12,13,14,16,17,18,19,22,24,25,26,27,28,29,30,31,32,33,35,37,63,65,66,68) limit 28');

    }
}
