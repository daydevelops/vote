<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Daydevelops\Vote\Models\Vote;
use Daydevelops\Vote\Models\Voter;
use PHPUnit\Framework\TestFailure;
use Illuminate\Support\Facades\Event;
use Daydevelops\Vote\Events\VoterWeightChanged;
use Illuminate\Support\Facades\Config;

class CanVoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setup();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');

        $this->user = factory("Daydevelops\Vote\Tests\Models\User")->create();
        $this->comment = factory("Daydevelops\Vote\Tests\Models\Comment")->create([
            'user_id' => $this->user->id
        ]);
        Event::fake();

        Config::set('vote.allow_weight_changes',true);
        Config::set('vote.default_weight',1);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return ['Daydevelops\Vote\VoteServiceProvider'];
    }

    protected function signIn($user = null)
    {
        $user = $user ?: factory('Daydevelops\Vote\Tests\Models\User')->create();
        $this->be($user);
        return $this;
    }

    /** @test */
    public function a_user_knows_if_it_is_a_voter()
    {
        $this->assertFalse($this->user->isVoter());
        Voter::create(['user_id' => $this->user->id, 'weight' => 1]);
        $this->assertTrue($this->user->fresh()->isVoter());
    }


    /** @test */
    public function a_voter_is_created_for_a_user_voting_for_the_first_time()
    {
        $this->signIn();
        $this->assertCount(0, Voter::where(['user_id' => auth()->user()->id])->get());
        $this->comment->upvote();
        $this->assertCount(1, Voter::where(['user_id' => auth()->user()->id])->get());
        Vote::truncate();
        Voter::truncate();
        $this->assertCount(0, Voter::where(['user_id' => auth()->user()->id])->get());
        $this->comment->downVote();
        $this->assertCount(1, Voter::where(['user_id' => auth()->user()->id])->get());
    }

    /** @test */
    public function a_user_only_has_one_voter()
    {
        $this->assertCount(0, Voter::where(['user_id' => $this->user->id])->get());
        $this->user->makeVoter();
        $this->user->makeVoter();
        $this->assertCount(1, Voter::where(['user_id' => $this->user->id])->get());
    }


    /** @test */
    public function a_user_knows_its_vote_weight()
    {
        Voter::create(['user_id' => $this->user->id, 'weight' => 10]);
        $this->assertEquals(10, $this->user->voter->weight);
    }

    /** @test */
    public function a_users_voter_weight_can_increase()
    {
        Voter::create(['user_id' => $this->user->id, 'weight' => 1]);
        $this->assertEquals(1, $this->user->voter->weight);
        $this->user->voter->addVoteWeight(3);
        $this->assertEquals(4, $this->user->voter->weight);
    }

    /** @test */
    public function a_users_voter_weight_can_decrease()
    {
        Voter::create(['user_id' => $this->user->id, 'weight' => 5]);
        $this->assertEquals(5, $this->user->voter->weight);
        $this->user->voter->addVoteWeight(-3);
        $this->assertEquals(2, $this->user->voter->weight);
    }

    /** @test */
    public function a_users_vote_weight_has_a_minimum_of_zero()
    {
        Voter::create(['user_id' => $this->user->id, 'weight' => 5]);
        $this->assertEquals(5, $this->user->voter->weight);
        $this->user->voter->addVoteWeight(-8);
        $this->assertEquals(0, $this->user->voter->weight);
    }

    /** @test */
    public function a_user_has_a_score_calculated_from_their_votable_items()
    {
        $this->signIn();
        $this->comment->upVote();
        $this->signIn();
        $this->comment->upVote();
        $this->signIn();
        $this->comment->upVote();
        $this->assertEquals(3, $this->user->votable_score);
    }

    /** @test */
    public function an_event_is_triggered_for_a_change_in_vote_weight()
    {
        $voter = $this->user->makeVoter();
        $voter->addVoteWeight(2);
        Event::assertDispatched(VoterWeightChanged::class, function ($event) use ($voter) {
            return $event->voter->id === $voter->id;
        });
    }

    /** @test */
    public function no_vote_is_cast_if_the_users_vote_weight_is_zero()
    {
        $this->signIn();
        $voter = auth()->user()->makeVoter();
        $voter->addVoteWeight(-1); // resulting in zero
        $this->comment->upVote();
        $this->assertCount(0, Vote::all());
    }
    
}
