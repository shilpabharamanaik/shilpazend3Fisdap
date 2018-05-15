<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Update some product prices per MM, MRAPI-356
 */
class Version20150903103405 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // update unlimited scheduler pricing for nursing
        $this->addSql('UPDATE fisdap2_product SET price=55 WHERE profession_id = 2 AND configuration=2 LIMIT 1');

        // update package pricing for sonography
        $this->addSql('UPDATE fisdap2_product_package SET price=100 WHERE id = 16 AND certification_id=15 LIMIT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // reset unlimited scheduler pricing for nursing
        $this->addSql('UPDATE fisdap2_product SET price=50 WHERE profession_id = 2 AND configuration=2 LIMIT 1');

        // reset package pricing for sonography
        $this->addSql('UPDATE fisdap2_product_package SET price=40 WHERE id = 16 AND certification_id=15 LIMIT 1');
    }
}
