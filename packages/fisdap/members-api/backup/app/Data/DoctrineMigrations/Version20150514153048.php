<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150514153048 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        //setup quiz part 1
        //create entry in MoodleTestData
        $this->addSql("INSERT INTO MoodleTestData SET Blueprint_id = 177, MoodleQuiz_id = 67, TestName = 'ECU - Paramedic Readiness Exam Part 1', Active = 4, MoodleCourse_id = 18, ShowDetails = 1, MoodleDatabase = 'fis_moodle', MoodlePrefix = 'fismdl', PublishScores = 0, MoodleIDMod = 0, CertLevel = 'year3'");

        //Create entries in ItemMoodleMap to tie moodle item ids to fisdap item ids
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6506, Item_id = 18769, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6507, Item_id = 20321, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6508, Item_id = 20322, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6509, Item_id = 20323, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6510, Item_id = 20324, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6511, Item_id = 20325, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6512, Item_id = 20326, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6513, Item_id = 20327, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6514, Item_id = 20328, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6515, Item_id = 20329, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6516, Item_id = 20330, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6517, Item_id = 20331, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6518, Item_id = 20332, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6519, Item_id = 20333, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6520, Item_id = 20334, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6521, Item_id = 20335, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6522, Item_id = 20336, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6523, Item_id = 20337, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6524, Item_id = 20338, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6525, Item_id = 20339, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6526, Item_id = 20340, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6527, Item_id = 20341, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6528, Item_id = 20342, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6529, Item_id = 20343, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6530, Item_id = 20344, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6531, Item_id = 20345, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6532, Item_id = 20346, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6533, Item_id = 20347, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6534, Item_id = 20348, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6535, Item_id = 20349, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6536, Item_id = 20350, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6537, Item_id = 20351, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6538, Item_id = 18790, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6539, Item_id = 18792, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6540, Item_id = 20352, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6541, Item_id = 20353, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6542, Item_id = 20354, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6543, Item_id = 20355, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6544, Item_id = 20356, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6545, Item_id = 20357, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6546, Item_id = 20358, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6547, Item_id = 20360, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6548, Item_id = 20361, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6549, Item_id = 20362, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6550, Item_id = 20363, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6551, Item_id = 20364, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6552, Item_id = 20365, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6553, Item_id = 20366, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6554, Item_id = 20368, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6555, Item_id = 20369, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6556, Item_id = 20370, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6557, Item_id = 20371, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6558, Item_id = 20372, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6559, Item_id = 20373, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6560, Item_id = 20374, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6561, Item_id = 20375, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6562, Item_id = 20376, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6563, Item_id = 20377, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6564, Item_id = 20378, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6565, Item_id = 20379, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6566, Item_id = 20380, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6567, Item_id = 20381, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6568, Item_id = 20406, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6569, Item_id = 20407, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6570, Item_id = 20385, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6571, Item_id = 20386, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6572, Item_id = 20387, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6573, Item_id = 20388, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6574, Item_id = 20389, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6575, Item_id = 20390, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6576, Item_id = 20391, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6577, Item_id = 20392, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6578, Item_id = 20393, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6579, Item_id = 20394, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6580, Item_id = 20395, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6581, Item_id = 20397, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6582, Item_id = 20398, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6583, Item_id = 20399, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6584, Item_id = 20400, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6585, Item_id = 20401, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6586, Item_id = 20402, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6587, Item_id = 20403, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6588, Item_id = 20404, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6589, Item_id = 20405, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6590, Item_id = 20408, MoodleQuiz_id = 67, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");

        //Link the product to the moodle quiz
        $this->addSql("insert into fisdap2_products_moodle_quizzes (product_id, moodle_quiz_id) values (47, 67)");

        //setup quiz part 2
        //create entry in MoodleTestData
        $this->addSql("INSERT INTO MoodleTestData SET Blueprint_id = 178, MoodleQuiz_id = 68, TestName = 'ECU - Paramedic Readiness Exam Part 2', Active = 4, MoodleCourse_id = 18, ShowDetails = 1, MoodleDatabase = 'fis_moodle', MoodlePrefix = 'fismdl', PublishScores = 0, MoodleIDMod = 0, CertLevel = 'year3'");

        //Create entries in ItemMoodleMap to tie moodle item ids to fisdap item ids
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6591, Item_id = 20441, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6592, Item_id = 20442, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6593, Item_id = 20443, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6594, Item_id = 20444, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6595, Item_id = 20445, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6596, Item_id = 20446, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6597, Item_id = 20447, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6598, Item_id = 20448, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6599, Item_id = 20449, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6600, Item_id = 20450, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6601, Item_id = 20451, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6602, Item_id = 20452, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6603, Item_id = 20453, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6604, Item_id = 20454, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6605, Item_id = 20455, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6606, Item_id = 20456, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6607, Item_id = 20457, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6608, Item_id = 20458, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6609, Item_id = 20459, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6610, Item_id = 20460, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6611, Item_id = 20461, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6612, Item_id = 20462, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6613, Item_id = 20463, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6614, Item_id = 20464, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6615, Item_id = 20465, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6616, Item_id = 20466, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6617, Item_id = 20467, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6618, Item_id = 20468, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6619, Item_id = 20469, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6620, Item_id = 20470, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6621, Item_id = 20471, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6622, Item_id = 20472, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6623, Item_id = 20409, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6624, Item_id = 20410, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6625, Item_id = 20411, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6626, Item_id = 20412, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6627, Item_id = 20413, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6628, Item_id = 20414, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6629, Item_id = 20415, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6630, Item_id = 20416, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6631, Item_id = 20417, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6632, Item_id = 20418, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6633, Item_id = 20419, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6634, Item_id = 20420, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6635, Item_id = 20421, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6636, Item_id = 20422, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6637, Item_id = 20423, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6638, Item_id = 20424, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6639, Item_id = 20425, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6640, Item_id = 20426, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6641, Item_id = 20427, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6642, Item_id = 20428, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6643, Item_id = 20429, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6644, Item_id = 20430, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6645, Item_id = 20431, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6646, Item_id = 20432, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6647, Item_id = 20433, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6648, Item_id = 20434, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6649, Item_id = 20435, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6650, Item_id = 20436, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6651, Item_id = 20437, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6652, Item_id = 20438, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6653, Item_id = 20439, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6654, Item_id = 20440, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6655, Item_id = 20473, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6656, Item_id = 20474, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6657, Item_id = 20475, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6658, Item_id = 20476, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6659, Item_id = 20477, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6660, Item_id = 20478, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6661, Item_id = 20479, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6662, Item_id = 20480, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6663, Item_id = 20481, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6664, Item_id = 20482, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6665, Item_id = 20483, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6666, Item_id = 20484, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6667, Item_id = 20485, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6668, Item_id = 20486, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6669, Item_id = 20487, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6670, Item_id = 20488, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6671, Item_id = 20489, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6672, Item_id = 20490, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6673, Item_id = 20491, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6674, Item_id = 20492, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");
        $this->addSql("insert into ItemMoodleMap set Moodle_id = 6675, Item_id = 20493, MoodleQuiz_id = 68, MoodleDBName = 'fis_moodle', MoodleDBPrefix = 'fismdl'");

        //Link the product to the moodle quiz
        $this->addSql("insert into fisdap2_products_moodle_quizzes (product_id, moodle_quiz_id) values (47, 68)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        //remove both exams from MoodleTestData (we can use just the MoodleQuiz_id because these should never overlap in this table)
        $this->addSql("delete from MoodleTestData where MoodleQuiz_id in (67, 68) limit 2;");

        //remove all item associations from ItemMoodleMap (have to specify quiz_id and DBName, because this table has unmodified quiz ids which may lead to collisions between different DBs)
        $this->addSql("delete from ItemMoodleMap where MoodleQuiz_id in (67,68) and MoodleDBName = 'fis_moodle' limit 170");

        //remove product association
        $this->addSql("delete from fisdap2_products_moodle_quizzes where product_id = 47 and moodle_quiz_id in (67,68) limit 2");
    }
}
