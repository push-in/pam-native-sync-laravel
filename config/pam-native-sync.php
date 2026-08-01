<?php
declare(strict_types=1);
return ['path'=>'pam-native/sync','middleware'=>['api','auth:sanctum','throttle:60,1'],'max_operations'=>100,'max_pull'=>500,'cursor_key'=>env('APP_KEY'),'retention_days'=>30];
