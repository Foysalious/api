<?php namespace Sheba\AutoSpAssign;


use Sheba\Helpers\ConstGetter;

class QueryAliases
{
    use ConstGetter;

    const AVG_RATING = 'avg_rating';
    const JOB_COUNT = 'job_count';
    const COMPLAIN_COUNT = 'complain_count';
    const ITA_COUNT = 'ita_count';
    const OTA_COUNT = 'ota_count';
    const SCHEDULE_JOB_COUNT = 'schedule_due_job_count';
    const SPO_USAGE_COUNT = 'resource_app_usage_count';
    const MAX_REVENUE = 'max_rev';
    const PARTNER_ID = 'partner_id';
    const IMPRESSION_COUNT = 'impression_count';
}