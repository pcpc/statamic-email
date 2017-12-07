<?php

namespace Statamic\Addons\Ministry;

use Statamic\Extend\Filter;

class MinistryFilter extends Filter
{
    /**
     * Perform filtering on a collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function filter()
    {
        return $this->collection->filter(function ($entry) {
            return str_contains(
                $entry->get('ministry'),
                $this->get('minfilter', '')
            );
        });
    }
}
