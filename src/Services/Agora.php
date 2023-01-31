<?php

namespace Queents\LaravelAgora\Services;

use Exception;
use Queents\LaravelAgora\Services\Token\RtcTokenBuilder;

class Agora
{
    protected string|int $id;
    protected string|null $channel="agora";
    protected bool $audio=false;
    protected bool $join = false;

    public static function make(string|int $id): static
    {
        return (new self())->id($id);
    }

    public function id(string|int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function join(bool|null $join= true): static
    {
        $this->join = $join;
        return $this;
    }

    public function audioOnly(bool|null $audio=true): static
    {
        $this->audio = $audio;
        return $this;
    }

    public function channel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function token()
    {
        $appID = config('laravel-agora.agora.app_id');
        $appCertificate = config('laravel-agora.agora.app_certificate');

        if($appID && $appCertificate){
            $channelName = $this->channel . '.'. $this->id;
            if ($this->join) {
                $role = RtcTokenBuilder::$roles['RoleSubscriber'];
            } else {
                $role = RtcTokenBuilder::$roles['RolePublisher'];
            }

            //Build a Time
            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            //Generate UID
            $uid = rand(999, 1999);


            $token = RtcTokenBuilder::build(
                appID: $appID,
                appCertificate: $appCertificate,
                channelName: $channelName,
                uid: $uid,
                role: $role,
                privilegeExpireTs: $privilegeExpiredTs,
                type: $this->audio ? 'audio' : 'video'
            );

            return $token;
        }

        abort(400, 'Sorry Agora API Key, or Certificate not Exists');
    }
}
