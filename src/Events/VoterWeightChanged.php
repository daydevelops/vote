<?php

namespace Daydevelops\Vote\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoterWeightChanged
{
    use Dispatchable, SerializesModels;

    /**
     * The voter that was updated.
     *
     * @var Voter
     */
    public $voter;

    /**
     * Create a new voter weight changed instance.
     *
     * @param Voter $voter
     */
    public function __construct($voter)
    {
        $this->voter = $voter;
    }
}
