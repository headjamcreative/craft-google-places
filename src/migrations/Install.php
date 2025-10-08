<?php

namespace headjam\craftgoogleplaces\migrations;

use craft\db\Migration;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    public function safeDown(): bool
    {
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables(): void
    {
        $this->archiveTableIfExists('{{%craftgoogleplaces_googleplaces}}');
        $this->createTable('{{%craftgoogleplaces_googleplaces}}', [
            'id' => $this->primaryKey(),
            'placeId' => $this->string()->notNull()->unique(),
            'displayName' => $this->string()->notNull(),
            'nationalPhoneNumber' => $this->string(),
            'formattedAddress' => $this->text(),
            'locationLatitude' => $this->double(),
            'locationLongitude' => $this->double(),
            'googleMapsLinksReviewsUri' => $this->text(),
            'websiteUri' => $this->text(),
            'regularOpeningHours' => $this->text(),
            'updated' => $this->timestamp()->notNull(),
        ]);
        $this->createIndex('idx_place_placeId', '{{%craftgoogleplaces_googleplaces}}', 'placeId');
    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists('{{%craftgoogleplaces_googleplaces}}');
    }
}
