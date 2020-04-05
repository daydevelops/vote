<?php

namespace Daydevelops\Vote\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemDownVoted
{
    use Dispatchable, SerializesModels;

    /**
     * The vote that was created.
     *
     * @var Vote
     */
    public $vote;

    /**
     * Create a new item downvoted instance.
     *
     * @param Vote $vote
     */
    public function __construct($vote)
    {
        $this->vote = $vote;
    }
}
