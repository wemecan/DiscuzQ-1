<?php

/**
 * Discuz & Tencent Cloud
 * This is NOT a freeware, use is subject to license terms
 */

namespace App\Events\Thread;

use App\Models\Thread;
use App\Models\User;

class ThreadWasApproved
{
    /**
     * The thread that was approved.
     *
     * @var Thread
     */
    public $thread;

    /**
     * @var User
     */
    public $actor;

    /**
     * @var array
     */
    public $data;

    /**
     * @param Thread $thread
     * @param User $actor
     * @param array $data
     */
    public function __construct(Thread $thread, User $actor, array $data = [])
    {
        $this->thread = $thread;
        $this->actor = $actor;
        $this->data = $data;
    }
}
