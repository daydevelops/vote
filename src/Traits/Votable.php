<?php

namespace Daydevelops\Vote\Traits;

use Daydevelops\Vote\Events\ItemUpVoted;
use Daydevelops\Vote\Events\ItemDownVoted;
use Daydevelops\Vote\Models\Vote;;

trait Votable
{
    /**
     * add the score attribute to the votable object
     *
     * @return void
     */
    public function initializeAppendAttributeTrait()
    {
        $this->append('score');
    }

    public function getScoreAttribute()
    {
        return $this->votes()->sum('value');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'voted_id')->where('voted_type', __CLASS__);
    }

    /**
     * if the votable object has a user creator, return the user_id.
     * 
     * This method is used to append the user_id of the votable object to the casted vote.
     * This redundency is necessary when calculating the votable_score attribute for the Voter class
     *
     * @return mixed
     */
    public function getUserID()
    {
        return isset($this->user_id) ? $this->user_id : null;
    }

    /**
     * create an upvote for this object.
     * 
     * If the current voter casting the vote has already downvoted this object,
     * their downvote will be destroyed and replaced with an upvote.
     *
     * @return Vote $vote
     */
    public function upVote()
    {
        $user = auth()->user();

        if ($this->hasUpVoted() || $this->user_id == $user->id) {
            return false;
        }

        if (!$user->isVoter()) {
            $user->makeVoter();
        }

        if ($this->hasDownVoted()) {
            Vote::where([
                'user_id' => $user->id,
                'voted_id' => $this->id,
                'voted_type' => __CLASS__
            ])->delete();
        }

        // create upvote
        $vote = Vote::create([
            'user_id' => $user->id,
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'votable_user_id' => $this->getUserID(),
            'value' => $user->voteWeight()
        ]);

        event(new ItemUpVoted($vote));

        return $vote;
    }

    /**
     * create a downvote for this object.
     * 
     * If the current voter casting the vote has already upvoted this object,
     * their upvote will be destroyed and replaced with a downvote.
     *
     * @return Vote $vote
     */
    public function downVote()
    {

        $user = auth()->user();

        if ($this->hasDownVoted() || $this->user_id == $user->id) {
            return false;
        }

        if (!$user->isVoter()) {
            $user->makeVoter();
        }

        if ($this->hasUpVoted()) {
            Vote::where([
                'user_id' => $user->id,
                'voted_id' => $this->id,
                'voted_type' => __CLASS__
            ])->delete();
        }
        // create downvote
        $vote = Vote::create([
            'user_id' => $user->id,
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'votable_user_id' => $this->getUserID(),
            'value' => -1 * $user->voteWeight()
        ]);

        event(new ItemDownVoted($vote));

        return $vote;
    }

    public function hasVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->exists();
    }

    public function hasDownVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->where('value','<',0)->exists();
    }

    public function hasUpVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->where('value','>',0)->exists();
    }
}
