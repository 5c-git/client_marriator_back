<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap custom validation rules.
     */
    public function boot(): void
    {
        $this->registerTimeEndOnOrBeforeDateEnd();
    }

    private function registerTimeEndOnOrBeforeDateEnd(): void
    {
        Validator::extend('time_end_on_or_before_date_end', function ($attribute, $value, $parameters, $validator) {
            $dateEnd = $validator->getData()['dateEnd'] ?? null;

            if (empty($value) || empty($dateEnd)) {
                return true;
            }

            try {
                $timeEndDate = Carbon::createFromFormat('d.m.Y H:i:s', $value)->startOfDay();
                $dateEndCarbon = Carbon::createFromFormat('d.m.Y', $dateEnd)->startOfDay();

                return $timeEndDate->lessThanOrEqualTo($dateEndCarbon);
            } catch (\Exception $e) {
                return false;
            }
        });

        Validator::replacer('time_end_on_or_before_date_end', function ($message, $attribute, $rule, $parameters) {
            return 'The activity end time must be on or before the activity end date.';
        });
    }
}
