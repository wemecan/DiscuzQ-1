<?php

/**
 * Discuz & Tencent Cloud
 * This is NOT a freeware, use is subject to license terms
 */

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;

/**
 * 回复通知
 *
 * Class Replied
 * @package App\Notifications
 */
class Replied extends System
{
    use Queueable;

    public $post;

    /**
     * @var
     */
    public $actor;

    /**
     * 当前驱动名称
     * @var
     */
    public $channel;

    /**
     * Replied constructor.
     *
     * @param Post $post
     * @param $actor
     * @param string $messageClass
     * @param array $build
     */
    public function __construct(Post $post, $actor, $messageClass = '', $build = [])
    {
        $this->setChannelName($messageClass);

        $this->post = $post;
        $this->actor = $actor;

        parent::__construct($messageClass, $build);
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $build = [
            'user_id' => $this->actor->id,
            'thread_id' => 0, // 必传
            'thread_username' => '', // 必传主题用户名
            'thread_title' => '',
            'thread_created_at' => '',
            'post_id' => $this->post->id,
            'post_content' => '',
            'reply_post_id' => 0, // 根据该字段判断是否是楼中楼
            'post_created_at' => '',
        ];

        $this->build($build);

        return $build;
    }

    /**
     * @param & $build
     */
    public function build(&$build)
    {
        $result = $this->post->getSummaryContent(Post::NOTICE_LENGTH);

        /**
         * 判断是否是楼中楼的回复
         */
        if ($this->post->reply_post_id) {
            $build['post_content'] = $result['content'];
            $build['post_created_at'] = $this->post->formatDate('created_at');
            // 回复的楼中楼数据
            $build['reply_post_id'] = $this->post->reply_post_id;
            $build['reply_post_user_id'] = $this->post->replyPost->user_id;
            $build['reply_post_content'] = $this->post->replyPost->formatContent();
            $build['reply_post_created_at'] = $this->post->replyPost->formatDate('created_at');
        } else {

            // 不是长文没有标题则使用首贴内容
            $firstContent = $result['first_content'];

            $build['post_content'] = $this->post->formatContent();  // 回复的内容
            $build['post_created_at'] = $this->post->formatDate('created_at');
        }

        // 主题数据
        $build['thread_id'] = $this->post->thread->id;
        $build['thread_username'] = $this->post->thread->user->username;
        $build['thread_title'] = $firstContent ?? $result['first_content'];
        $build['thread_created_at'] = $this->post->thread->formatDate('created_at');
    }

    /**
     * 设置驱动名称
     *
     * @param $strClass
     */
    protected function setChannelName($strClass)
    {
        switch ($strClass) {
            case 'App\MessageTemplate\Wechat\WechatRepliedMessage':
                $this->channel = 'wechat';
                break;
            case 'App\MessageTemplate\RepliedMessage':
            default:
                $this->channel = 'database';
                break;
        }
    }

}
