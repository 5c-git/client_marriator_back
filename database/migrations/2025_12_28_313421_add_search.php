<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = "CREATE TABLE `search_request` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `place_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `status` smallint NOT NULL DEFAULT '0',
  `self_employed` tinyint(1) NOT NULL DEFAULT '0',
  `radius` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activity_id` bigint DEFAULT NULL,
  `task_id` bigint DEFAULT NULL,
  `view_activity_id` bigint unsigned NOT NULL,
  `count` int NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `need_foto` tinyint(1) NOT NULL,
  `date_activity` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_request_user_id_index` (`user_id`),
  KEY `search_request_order_id_index` (`order_id`),
  KEY `search_request_activity_id_index` (`activity_id`),
  KEY `search_request_task_id_index` (`task_id`),
  KEY `search_request_view_activity_id_index` (`view_activity_id`)
)";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_request');
    }
};
