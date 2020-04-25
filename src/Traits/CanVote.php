<?php

namespace Daydevelops\Vote\Traits;

use Daydevelops\Vote\Models\Vote;
use Daydevelops\Vote\Models\Voter;

trait CanVote
{

    /**
     * add the votable_score attribute to the user
     *
     * @return void
     */
    public function initializeAppendAttributeTrait()
    {
        if (config('vote.user_has_score')) {
            $this->append('votable_score');
        }
    }

    /**
     * Calculate the total vote score of each votable object the user has created
     *
     * @return int
     */
    public function getVotableScoreAttribute()
    {
        return Vote::where(['votable_user_id' => $this->id])->sum('value');
    }

    public function voter()
    {
        return $this->hasOne(Voter::class);
    }

    /**
     * Is there a Voter object for this user yet?
     *
     * @return bool
     */
    public function isVoter()
    {
        return $this->voter()->exists();
    }

    /**
     * Create a Voter object for this user
     * 
     * @param int $weight
     * @return mixed
     */
    public function makeVoter($weight = null)
    {
        if (!$this->isVoter()) {
            $weight = $weight!==null ? $weight : config('vote.default_weight');
            return Voter::create(['user_id' => $this->id, 'weight' => $weight]);
        } else {
            return null;
        }
    }
}
