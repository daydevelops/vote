<?php

namespace Daydevelops\Vote\Traits;

use Daydevelops\Vote\Models\Vote;
use Daydevelops\Vote\Models\Voter;
use Daydevelops\Vote\Events\VoterWeightChanged;

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

    protected function getVoter()
    {
        return Voter::where(['user_id' => $this->id])->first();
    }

    /**
     * Is there a Voter object for this user yet?
     *
     * @return bool
     */
    public function isVoter()
    {
        return !!$this->getVoter();
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
            $weight = $weight ? $weight : config('vote.default_weight');
            return Voter::create(['user_id' => $this->id, 'weight' => $weight]);
        } else {
            return null;
        }
    }

    public function voteWeight()
    {
        if ($this->isVoter()) {
            return Voter::where(['user_id' => $this->id])->first()->weight;
        } else {
            return null;
        }
    }

    /**
     * update the users vote weight by adding a positive or negative change
     *
     * @param int $change
     * @return Voter $voter
     */
    public function addVoteWeight($change)
    {
        $voter = $this->getVoter();

        if (!config('vote.allow_weight_changes')) {
            return $voter;
        }

        // update voter weight, or make a new voter if none exists
        if (!!$voter) {
            $weight = max(0, $voter->weight + $change); // set a minimum of zero
            $voter->update(['weight' => $weight]);
        } else {
            $this->makeVoter($change);
        }

        $voter = $this->getVoter();
        event(new VoterWeightChanged($voter));
        return $voter;
    }
}
