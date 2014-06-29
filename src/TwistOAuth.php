<?php

/**
 * Main class.
 */
final class TwistOAuth {
    
    /**
     * Request options.
     * 
     * @const MODE_DEFAULT       for various endpoints
     * @const MODE_REQUEST_TOKEN "oauth/request_token"
     * @const MODE_ACCESS_TOKEN  "oauth/access_token"
     */
    const MODE_DEFAULT       = 0;
    const MODE_REQUEST_TOKEN = 1;
    const MODE_ACCESS_TOKEN  = 2;
    
    /**
     * OAuth parameters.
     * 
     * @property-read string $ck ConsumerKey
     * @property-read string $cs ConsumerSecret
     * @property-read string $ot RequestToken or AccessToken 
     * @property-read string $os RequestTokenSecret or AccessTokenSecret
     */
    private $ck = ''; 
    private $cs = '';
    private $ot = '';
    private $os = '';
    
    /**
     * A flag for reinitialize prevention.
     * 
     * @property bool $constructed
     */
    private $constructed = false;
    
    /**
     * Parse endpoint url.
     * 
     * @param string $endpoint
     * @return string URL
     */
    public static function url($endpoint) {
        static $from;
        static $to;
        if (!$from) {
            $from = array(
                '@\Aaccount/login_verification_request\z@',
                '@\Aaccount/login_verification_enrollment__post\z@',
                '@\Apush_destinations/enable_login_verification\z@',
                '@\Aaccount/remove_profile_banner\z@',
                '@\Aaccount/settings\z@',
                '@\Aaccount/update_delivery_device\z@',
                '@\Aaccount/update_profile\z@',
                '@\Aaccount/update_profile_background_image\z@',
                '@\Aaccount/update_profile_banner\z@',
                '@\Aaccount/update_profile_colors\z@',
                '@\Aaccount/update_profile_image\z@',
                '@\Aaccount/verification\z@',
                '@\Aaccount/verify_credentials\z@',
                '@\Aactivity/about_me\z@',
                '@\Aactivity/by_friends\z@',
                '@\Aapplication/rate_limit_status\z@',
                '@\Abeta/timelines/custom/list\z@',
                '@\Abeta/timelines/custom/show\z@',
                '@\Abeta/timelines/timeline\z@',
                '@\Abeta/timelines/custom/create\z@',
                '@\Abeta/timelines/custom/update\z@',
                '@\Abeta/timelines/custom/destroy\z@',
                '@\Abeta/timelines/custom/add\z@',
                '@\Abeta/timelines/custom/remove\z@',
                '@\Ablocks/create\z@',
                '@\Ablocks/destroy\z@',
                '@\Ablocks/ids\z@',
                '@\Ablocks/list\z@',
                '@\Aconversation/show/(\d++)\z@',
                '@\Adevice/token\z@',
                '@\Adevice_following/ids\z@',
                '@\Adevice_following/list\z@',
                '@\Adirect_messages\z@',
                '@\Adirect_messages/destroy\z@',
                '@\Adirect_messages/new\z@',
                '@\Adirect_messages/sent\z@',
                '@\Adirect_messages/show\z@',
                '@\Adirect_messages/read\z@',
                '@\Adiscover/highlight\z@',
                '@\Adiscover/home\z@',
                '@\Adiscover/nearby\z@',
                '@\Adiscover/universal\z@',
                '@\Afavorites/create\z@',
                '@\Afavorites/destroy\z@',
                '@\Afavorites/list\z@',
                '@\Afollowers/ids\z@',
                '@\Afollowers/list\z@',
                '@\Afriends/ids\z@',
                '@\Afriends/list\z@',
                '@\Afriendships/create\z@',
                '@\Afriendships/destroy\z@',
                '@\Afriendships/incoming\z@',
                '@\Afriendships/lookup\z@',
                '@\Afriendships/no_retweets/ids\z@',
                '@\Afriendships/outgoing\z@',
                '@\Afriendships/show\z@',
                '@\Afriendships/update\z@',
                '@\Ageo/id/(\d++)\z@',
                '@\Ageo/place\z@',
                '@\Ageo/reverse_geocode\z@',
                '@\Ageo/search\z@',
                '@\Ageo/similar_places\z@',
                '@\Ahelp/configuration\z@',
                '@\Ahelp/experiments\z@',
                '@\Ahelp/languages\z@',
                '@\Ahelp/privacy\z@',
                '@\Ahelp/tos\z@',
                '@\Alists/create\z@',
                '@\Alists/destroy\z@',
                '@\Alists/list\z@',
                '@\Alists/members\z@',
                '@\Alists/members/create\z@',
                '@\Alists/members/create_all\z@',
                '@\Alists/members/destroy\z@',
                '@\Alists/members/destroy_all\z@',
                '@\Alists/members/show\z@',
                '@\Alists/memberships\z@',
                '@\Alists/ownerships\z@',
                '@\Alists/show\z@',
                '@\Alists/statuses\z@',
                '@\Alists/subscribers\z@',
                '@\Alists/subscribers/create\z@',
                '@\Alists/subscribers/destroy\z@',
                '@\Alists/subscribers/show\z@',
                '@\Alists/subscriptions\z@',
                '@\Alists/update\z@',
                '@\Amutes/users/create\z@',
                '@\Amutes/users/destroy\z@',
                '@\Amutes/users/ids\z@',
                '@\Amutes/users/list\z@',
                '@\Asaved_searches/create\z@',
                '@\Asaved_searches/destroy/(\d++)\z@',
                '@\Asaved_searches/list\z@',
                '@\Asaved_searches/show/(\d++)\z@',
                '@\Ascheduled/list\z@',
                '@\Ascheduled/lookup\z@',
                '@\Ascheduled/show/(\d++)\z@',
                '@\Asearch/tweets\z@',
                '@\Asearch/typeahead\z@',
                '@\Asearch/universal\z@',
                '@\Astatuses/(\d++)/activity/summary\z@',
                '@\Astatuses/destroy/(\d++)\z@',
                '@\Astatuses/home_timeline\z@',
                '@\Astatuses/lookup\z@',
                '@\Astatuses/media_timeline\z@',
                '@\Astatuses/mentions_timeline\z@',
                '@\Astatuses/oembed\z@',
                '@\Astatuses/retweet/(\d++)\z@',
                '@\Astatuses/retweeters/ids\z@',
                '@\Astatuses/retweets/(\d++)\z@',
                '@\Astatuses/retweets_of_me\z@',
                '@\Astatuses/show/(\d++)\z@',
                '@\Astatuses/update\z@',
                '@\Astatuses/update_with_media\z@',
                '@\Astatuses/user_timeline\z@',
                '@\Atimeline/home\z@',
                '@\Atranslations/show\z@',
                '@\Atrends/available\z@',
                '@\Atrends/closest\z@',
                '@\Atrends/personalized\z@',
                '@\Atrends/place\z@',
                '@\Atrends/timeline\z@',
                '@\Ausers/contributees\z@',
                '@\Ausers/contributors\z@',
                '@\Ausers/lookup\z@',
                '@\Ausers/profile_banner\z@',
                '@\Ausers/recommendations\z@',
                '@\Ausers/report_spam\z@',
                '@\Ausers/reverse_lookup\z@',
                '@\Ausers/search\z@',
                '@\Ausers/show\z@',
                '@\Ausers/suggestions\z@',
                '@\Ausers/suggestions/([^/]++)\z@',
                '@\Ausers/suggestions/([^/]++)/members\z@',
                '@\Ausers/wipe_addressbook\z@',
                '@\Ai/activity/about_me\z@',
                '@\Ai/activity/by_friends\z@',
                '@\Ai/statuses/(\d++)/activity/summary\z@',
                '@\Aoauth/access_token\z@',
                '@\Aoauth/authenticate\z@',
                '@\Aoauth/authorize\z@',
                '@\Aoauth/request_token\z@',
                '@\Asite\z@',
                '@\Astatuses/filter\z@',
                '@\Astatuses/firehose\z@',
                '@\Astatuses/sample\z@',
                '@\Auser\z@',
                '@\Amedia/upload\z@',
                '@\Aaccount/generate\z@',
                '@\Aurls/count\z@',
                '@\Aaccount/push_destinations/device\z@',
                '@\Aprompts/suggest\z@',
            );
            $to = array(
                'https://api.twitter.com/1.1/account/login_verification_request.json',
                'https://api.twitter.com/1.1/account/login_verification_request__post.json',
                'https://api.twitter.com/1.1/push_destinations/enable_login_verification.json',
                'https://api.twitter.com/1.1/account/remove_profile_banner.json',
                'https://api.twitter.com/1.1/account/settings.json',
                'https://api.twitter.com/1.1/account/update_delivery_device.json',
                'https://api.twitter.com/1.1/account/update_profile.json',
                'https://api.twitter.com/1.1/account/update_profile_background_image.json',
                'https://api.twitter.com/1.1/account/update_profile_banner.json',
                'https://api.twitter.com/1.1/account/update_profile_colors.json',
                'https://api.twitter.com/1.1/account/update_profile_image.json',
                'https://api.twitter.com/1.1/account/verification.json',
                'https://api.twitter.com/1.1/account/verify_credentials.json',
                'https://api.twitter.com/1.1/activity/about_me.json',
                'https://api.twitter.com/1.1/activity/by_friends.json',
                'https://api.twitter.com/1.1/application/rate_limit_status.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/list.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/show.json',
                'https://api.twitter.com/1.1/beta/timelines/timeline.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/create.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/update.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/destroy.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/add.json',
                'https://api.twitter.com/1.1/beta/timelines/custom/remove.json',
                'https://api.twitter.com/1.1/blocks/create.json',
                'https://api.twitter.com/1.1/blocks/destroy.json',
                'https://api.twitter.com/1.1/blocks/ids.json',
                'https://api.twitter.com/1.1/blocks/list.json',
                'https://api.twitter.com/1.1/conversation/show/$1.json',
                'https://api.twitter.com/1.1/device/token.json',
                'https://api.twitter.com/1.1/device_following/ids.json',
                'https://api.twitter.com/1.1/device_following/list.json',
                'https://api.twitter.com/1.1/direct_messages.json',
                'https://api.twitter.com/1.1/direct_messages/destroy.json',
                'https://api.twitter.com/1.1/direct_messages/new.json',
                'https://api.twitter.com/1.1/direct_messages/sent.json',
                'https://api.twitter.com/1.1/direct_messages/show.json',
                'https://api.twitter.com/1.1/direct_messages/read.json',
                'https://api.twitter.com/1.1/discover/highlight.json',
                'https://api.twitter.com/1.1/discover/home.json',
                'https://api.twitter.com/1.1/discover/nearby.json',
                'https://api.twitter.com/1.1/discover/universal.json',
                'https://api.twitter.com/1.1/favorites/create.json',
                'https://api.twitter.com/1.1/favorites/destroy.json',
                'https://api.twitter.com/1.1/favorites/list.json',
                'https://api.twitter.com/1.1/followers/ids.json',
                'https://api.twitter.com/1.1/followers/list.json',
                'https://api.twitter.com/1.1/friends/ids.json',
                'https://api.twitter.com/1.1/friends/list.json',
                'https://api.twitter.com/1.1/friendships/create.json',
                'https://api.twitter.com/1.1/friendships/destroy.json',
                'https://api.twitter.com/1.1/friendships/incoming.json',
                'https://api.twitter.com/1.1/friendships/lookup.json',
                'https://api.twitter.com/1.1/friendships/no_retweets/ids.json',
                'https://api.twitter.com/1.1/friendships/outgoing.json',
                'https://api.twitter.com/1.1/friendships/show.json',
                'https://api.twitter.com/1.1/friendships/update.json',
                'https://api.twitter.com/1.1/geo/id/$1.json',
                'https://api.twitter.com/1.1/geo/place.json',
                'https://api.twitter.com/1.1/geo/reverse_geocode.json',
                'https://api.twitter.com/1.1/geo/search.json',
                'https://api.twitter.com/1.1/geo/similar_places.json',
                'https://api.twitter.com/1.1/help/configuration.json',
                'https://api.twitter.com/1.1/help/experiments.json',
                'https://api.twitter.com/1.1/help/languages.json',
                'https://api.twitter.com/1.1/help/privacy.json',
                'https://api.twitter.com/1.1/help/tos.json',
                'https://api.twitter.com/1.1/lists/create.json',
                'https://api.twitter.com/1.1/lists/destroy.json',
                'https://api.twitter.com/1.1/lists/list.json',
                'https://api.twitter.com/1.1/lists/members.json',
                'https://api.twitter.com/1.1/lists/members/create.json',
                'https://api.twitter.com/1.1/lists/members/create_all.json',
                'https://api.twitter.com/1.1/lists/members/destroy.json',
                'https://api.twitter.com/1.1/lists/members/destroy_all.json',
                'https://api.twitter.com/1.1/lists/members/show.json',
                'https://api.twitter.com/1.1/lists/memberships.json',
                'https://api.twitter.com/1.1/lists/ownerships.json',
                'https://api.twitter.com/1.1/lists/show.json',
                'https://api.twitter.com/1.1/lists/statuses.json',
                'https://api.twitter.com/1.1/lists/subscribers.json',
                'https://api.twitter.com/1.1/lists/subscribers/create.json',
                'https://api.twitter.com/1.1/lists/subscribers/destroy.json',
                'https://api.twitter.com/1.1/lists/subscribers/show.json',
                'https://api.twitter.com/1.1/lists/subscriptions.json',
                'https://api.twitter.com/1.1/lists/update.json',
                'https://api.twitter.com/1.1/mutes/users/create.json',
                'https://api.twitter.com/1.1/mutes/users/destroy.json',
                'https://api.twitter.com/1.1/mutes/users/ids.json',
                'https://api.twitter.com/1.1/mutes/users/list.json',
                'https://api.twitter.com/1.1/saved_searches/create.json',
                'https://api.twitter.com/1.1/saved_searches/destroy/$1.json',
                'https://api.twitter.com/1.1/saved_searches/list.json',
                'https://api.twitter.com/1.1/saved_searches/show/$1.json',
                'https://api.twitter.com/1.1/scheduled/list.json',
                'https://api.twitter.com/1.1/scheduled/lookup.json',
                'https://api.twitter.com/1.1/scheduled/show/$1.json',
                'https://api.twitter.com/1.1/search/tweets.json',
                'https://api.twitter.com/1.1/search/typeahead.json',
                'https://api.twitter.com/1.1/search/universal.json',
                'https://api.twitter.com/1.1/statuses/$1/activity/summary.json',
                'https://api.twitter.com/1.1/statuses/destroy/$1.json',
                'https://api.twitter.com/1.1/statuses/home_timeline.json',
                'https://api.twitter.com/1.1/statuses/lookup.json',
                'https://api.twitter.com/1.1/statuses/media_timeline.json',
                'https://api.twitter.com/1.1/statuses/mentions_timeline.json',
                'https://api.twitter.com/1.1/statuses/oembed.json',
                'https://api.twitter.com/1.1/statuses/retweet/$1.json',
                'https://api.twitter.com/1.1/statuses/retweeters/ids.json',
                'https://api.twitter.com/1.1/statuses/retweets/$1.json',
                'https://api.twitter.com/1.1/statuses/retweets_of_me.json',
                'https://api.twitter.com/1.1/statuses/show/$1.json',
                'https://api.twitter.com/1.1/statuses/update.json',
                'https://api.twitter.com/1.1/statuses/update_with_media.json',
                'https://api.twitter.com/1.1/statuses/user_timeline.json',
                'https://api.twitter.com/1.1/timeline/home.json',
                'https://api.twitter.com/1.1/translations/show.json',
                'https://api.twitter.com/1.1/trends/available.json',
                'https://api.twitter.com/1.1/trends/closest.json',
                'https://api.twitter.com/1.1/trends/personalized.json',
                'https://api.twitter.com/1.1/trends/place.json',
                'https://api.twitter.com/1.1/trends/timeline.json',
                'https://api.twitter.com/1.1/users/contributees.json',
                'https://api.twitter.com/1.1/users/contributors.json',
                'https://api.twitter.com/1.1/users/lookup.json',
                'https://api.twitter.com/1.1/users/profile_banner.json',
                'https://api.twitter.com/1.1/users/recommendations.json',
                'https://api.twitter.com/1.1/users/report_spam.json',
                'https://api.twitter.com/1.1/users/reverse_lookup.json',
                'https://api.twitter.com/1.1/users/search.json',
                'https://api.twitter.com/1.1/users/show.json',
                'https://api.twitter.com/1.1/users/suggestions.json',
                'https://api.twitter.com/1.1/users/suggestions/$1.json',
                'https://api.twitter.com/1.1/users/suggestions/$1/members.json',
                'https://api.twitter.com/1.1/users/wipe_addressbook.json',
                'https://api.twitter.com/i/activity/about_me.json',
                'https://api.twitter.com/i/activity/by_friends.json',
                'https://api.twitter.com/i/statuses/$1/activity/summary.json',
                'https://api.twitter.com/oauth/access_token',
                'https://api.twitter.com/oauth/authenticate',
                'https://api.twitter.com/oauth/authorize',
                'https://api.twitter.com/oauth/request_token',
                'https://sitestream.twitter.com/1.1/site.json',
                'https://stream.twitter.com/1.1/statuses/filter.json',
                'https://stream.twitter.com/1.1/statuses/firehose.json',
                'https://stream.twitter.com/1.1/statuses/sample.json',
                'https://userstream.twitter.com/1.1/user.json',
                'https://upload.twitter.com/1.1/media/upload.json',
                'https://api.twitter.com/1/account/generate.json',
                'http://urls.api.twitter.com/1/urls/count.json',
                'https://api.twitter.com/1.1/push_destinations/device.json',
                'https://api.twitter.com/1.1/prompts/suggest.json',
            );
        }
        return preg_replace($from, $to, self::validateString('endpoint', $endpoint));
    }
    
    /**
     * Execute direct OAuth login.
     * 
     * @param string $ck consumer_key
     * @param string $cs consumer_secret
     * @param string $username screen_name or email
     * @param string $password
     * @return TwistOAuth
     * @throws TwistException
     */
    public static function login($ck, $cs, $username, $password) {
        $ch = self::curlInit();
        $to = new self($ck, $cs);
        $username = self::validateString('username', $username);
        $password = self::validateString('password', $password);
        $to = $to->renewWithRequestToken();
        self::curlSetOptForAuthenticityToken($ch, $to);
        $token = self::parseAuthenticityToken($ch, curl_exec($ch));
        self::curlSetOptForVerifier($ch, $to, $token, $username, $password);
        $verifier = self::parseVerifier($ch, curl_exec($ch));
        return $to->renewWithAccessToken($verifier);
    }
    
    /**
     * Execute multiple direct OAuth login.
     * 
     * @param array $credentials
     *     e.g.
     *     array(
     *         'foo' => array('CONSUMER_KEY_foo', 'CONSUMER_SECRET_foo', 'USERNAME_foo', 'PASSWORD_foo'),
     *         'bar' => array('CONSUMER_KEY_bar', 'CONSUMER_SECRET_bar', 'USERNAME_bar', 'PASSWORD_bar'),
     *         'baz' => array('CONSUMER_KEY_baz', 'CONSUMER_SECRET_baz', 'USERNAME_baz', 'PASSWORD_baz'),
     *         ...
     *     )
     * @return array
     *     e.g.
     *     array(
     *         'foo' => TwistOAuth object of foo,
     *         'bar' => TwistOAuth object of bar,
     *         'baz' => TwistOAuth object of baz,
     *         ...
     *     )
     * @throws TwistException
     */
    public static function multiLogin(array $credentials) {
        static $names = array('consumer_key', 'consumer_secret', 'username', 'password');
        $mh = curl_multi_init();
        $tos = $states = $chs = $schs = array();
        if (!$credentials) {
            return array();
        }
        foreach ($credentials as $i => &$credential) {
            if (!is_array($credential)) {
                throw new InvalidArgumentException(sprintf(
                    '(%s) The parameters must be array.',
                    $i
                ));
            }
            foreach ($names as $j => $name) {
                switch (true) {
                    case !isset($credential[$j]):
                    case false === $credential[$j] = filter_var($credential[$j]):
                        throw new InvalidArgumentException(sprintf(
                            '(%s) The parameter %s (%s) must be string.',
                            $i,
                            $j + 1,
                            $names[$j]
                        ));
                }
            }
            $tos[$i]    = new self($credential[0], $credential[1]);
            $states[$i] = 4;
            $chs[$i]    = $tos[$i]->curlPostRequestToken();
            $schs[$i]   = null;
            curl_multi_add_handle($mh, $chs[$i]);
        }
        unset($credential);
        while (CURLM_CALL_MULTI_PERFORM === $stat = curl_multi_exec($mh, $running));
        if (!$running || $stat !== CURLM_OK) {
            throw new TwistException('Failed to start multiple requests.');
        }
        do switch (curl_multi_select($mh, 10)) {
            case -1:
                usleep(10);
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
            case 0:
                continue 2;
            default:
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
                $add = false;
                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    if (false === $i = array_search($raised['handle'], $chs, true)) {
                        $i = array_search($raised['handle'], $schs, true);
                    }
                    try {
                        switch (--$states[$i]) {
                            case 3:
                                $obj = self::decode($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $tos[$i] = new self($tos[$i]->ck, $tos[$i]->cs, $obj->oauth_token, $obj->oauth_token_secret);
                                $schs[$i] = self::curlInit();
                                self::curlSetOptForAuthenticityToken($schs[$i], $tos[$i]);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                $add = !curl_multi_add_handle($mh, $schs[$i]);
                                break;
                            case 2:
                                $token = self::parseAuthenticityToken($raised['handle'], curl_multi_getcontent($raised['handle']));
                                self::curlSetOptForVerifier($raised['handle'], $tos[$i], $token, $credentials[$i][2], $credentials[$i][3]);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                $add = !curl_multi_add_handle($mh, $schs[$i]);
                                break;
                            case 1:
                                $verifier = self::parseVerifier($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $chs[$i] = $tos[$i]->curlPostAccessToken($verifier);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                $add = !curl_multi_add_handle($mh, $chs[$i]);
                                break;
                            case 0:
                                $obj = self::decode($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $tos[$i] = new self($tos[$i]->ck, $tos[$i]->cs, $obj->oauth_token, $obj->oauth_token_secret);
                                curl_multi_remove_handle($mh, $raised['handle']);
                        }
                    } catch (TwistException $e) {
                        throw new TwistException('(' . $i . ') ' . $e->getMessage(), $e->getCode());
                    }
                } while ($remains);
        } while ($running || $add);
        return $tos;
    }
    
    /**
     * Execute multiple cURL requests.
     * 
     * @param array $curls
     *     e.g.
     *     array(
     *         'foo' => cURL resource of foo
     *         'bar' => cURL resource of bar
     *         'baz' => cURL resource of baz
     *         ...
     *     )
     * @return array
     *     e.g.
     *     array(
     *         'foo' => stdClass or array,
     *         'bar' => stdClass or array,
     *         'baz' => stdClass or array,
     *         ...
     *     )
     * @throws TwistException
     */
    public static function curlMultiExec(array $curls) {
        return self::curlMultiExecAction($curls, false);
    }
    
    /**
     * Execute multiple cURL streaming requests.
     * 
     * @param array $curls
     *     e.g.
     *     array(
     *         'foo' => cURL resource of foo
     *         'bar' => cURL resource of bar
     *         'baz' => cURL resource of baz
     *         ...
     *     )
     * @throws TwistException
     */
    public static function curlMultiStreaming(array $curls) {
        self::curlMultiExecAction($curls, true);
    }
    
    /**
     * Decode response.
     * 
     * @param resource $ch cURL resource
     * @param string $response
     * @return stdClass|array|TwistImage
     * @throws TwistException
     */
    public static function decode($ch, $response) {
        $ch = self::validateCurl($ch);
        $response = self::validateString('response', $response);
        $info = curl_getinfo($ch);
        if (curl_errno($ch)) {
            throw new TwistException(curl_error($ch), $info['http_code']);
        }
        if (stripos($info['content_type'], 'image/') === 0) {
            return new TwistImage($info['content_type'], $response);
        }
        if (null !== $obj = json_decode($response)) {
            if (isset($obj->error)) {       
                throw new TwistException($obj->error, $info['http_code']);
            }
            if (isset($obj->errors)) {
                if (is_string($obj->errors)) {
                    throw new TwistException($obj->errors, $info['http_code']);
                } else {
                    throw new TwistException($obj->errors[0]->message, $info['http_code']);
                }
            }
            return $obj;
        }
        parse_str($response, $obj);
        $obj = (object)$obj;
        if (isset($obj->oauth_token, $obj->oauth_token_secret)) {
            return $obj;
        }
        if (preg_match("@Reason:\n<pre>([^<]++)</pre>@", $response, $matches)) {
            throw new TwistException(trim($matches[1]), $info['http_code']);
        }
        if (strip_tags($response) === $response) {
            throw new TwistException(trim($response), $info['http_code']);
        }
        throw new TwistException('Malformed response detected.', $info['http_code']);
    }
    
    /**
     * Constructor.
     *
     * @param string $ck ConsumerKey
     * @param string $cs ConsumerSecret
     * @param string [$ot] RequestToken or AccessToken 
     * @param string [$os] RequestTokenSecret or AccessTokenSecret
     */
    public function __construct($ck, $cs, $ot = '', $os = '') {
        if ($this->constructed) {
            throw new BadMethodCallExceptpion('Do not call __construct() by yourself.');
        }
        $this->constructed = true;
        $this->ck = self::validateString('ConsumerKey', $ck);
        $this->cs = self::validateString('ConsumerSecret', $cs);
        $this->ot = self::validateString('OAuthToken', $ot);
        $this->os = self::validateString('OAuthTokenSecret', $os);
    }
    
    /**
     * Getter for private properties.
     *
     * @name string property name
     * @return mixed
     */
    public function __get($name) {
        $name = filter_var($name);
        if (!property_exists($this, $name)) {
            throw new OutOfRangeException('Invalid property: ' . $name);
        }
        return $this->$name;
    }
    
    /**
     * Get URL for authentication.
     *
     * @param bool [$force_login]
     * @return string URL
     */
    public function getAuthenticateUrl($force_login = false) {
        $params = http_build_query(array(
            'oauth_token' => $this->ot,
            'force_login' => $force_login ? 1 : null,
        ), '', '&');
        return 'https://api.twitter.com/oauth/authenticate?' . $params;
    }
    
    /**
     * Get URL for authorization.
     *
     * @param bool [$force_login]
     * @return string URL
     */
    public function getAuthorizeUrl($force_login = false) {
        $params = http_build_query(array(
            'oauth_token' => $this->ot,
            'force_login' => $force_login ? 1 : null,
        ), '', '&');
        return 'https://api.twitter.com/oauth/authorize?' . $params;
    }
    
    /**
     * Execute GET request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @return stdClass|array
     * @throws TwistException
     */
    public function get($url, $params = array()) {
        $ch       = $this->curlGet($url, $params);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute GET OAuth Echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @return stdClass|array
     * @throws TwistException
     */
    public function getOut($url, $params = array()) {
        $ch       = $this->curlGetOut($url, $params);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute streaming GET request.
     *
     * @param string $url endpoint URL
     * @param callable $callback function for processing each message
     * @param array|string [$params] 1-demensional array or query string
     * @throws TwistException
     */
    public function streaming($url, $callback, $params = array()) {
        curl_exec($this->curlStreaming($url, $params, $callback));
        throw new TwistException('Streaming stopped.');
    }
    
    /**
     * Execute POST request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return stdClass|null
     * @throws TwistException
     */
    public function post($url, $params = array(), $wait_response = true) {
        $ch       = $this->curlPost($url, $params);
        $response = curl_exec($ch);
        if (!$wait_response) {
            return;
        }
        return self::decode($ch, $response);
    }
    
    /**
     * Execute POST OAuth Echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return stdClass|null
     * @throws TwistException
     */
    public function postOut($url, $params = array(), $wait_response = true) {
        $ch       = $this->curlPostOut($url, $params);
        $response = curl_exec($ch);
        if (!$wait_response) {
            return;
        }
        return self::decode($ch, $response);
    }
    
    /**
     * Execute POST request for "oauth/request_token".
     *
     * @return TwistOAuth
     * @throws TwistException
     */
    public function renewWithRequestToken() {
        $ch       = $this->curlPostRequestToken();
        $response = self::decode($ch, curl_exec($ch));
        return new self($this->ck, $this->cs, $response->oauth_token, $response->oauth_token_secret);
    }
    
    /**
     * Execute POST request for "oauth/access_token".
     *
     * @param string $oauth_verifier
     * @return TwistOAuth
     * @throws TwistException
     */
    public function renewWithAccessToken($oauth_verifier) {
        $ch       = $this->curlPostAccessToken($oauth_verifier);
        $response = self::decode($ch, curl_exec($ch));
        return new self($this->ck, $this->cs, $response->oauth_token, $response->oauth_token_secret);
    }
        
    /**
     * Execute multipart POST request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return stdClass|null
     * @throws TwistException
     */
    public function postMultipart($url, $params = array(), $wait_response = true) {
        $ch       = $this->curlPostMultipart($url, $params);
        $response = curl_exec($ch);
        if (!$wait_response) {
            return;
        }
        return self::decode($ch, $response);
    }
    
    /**
     * Execute multipart POST OAuth Echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return stdClass|null
     * @throws TwistException
     */
    public function postMultipartOut($url, $params = array(), $wait_response = true) {
        $ch       = $this->curlPostMultipartOut($url, $params);
        $response = curl_exec($ch);
        if (!$wait_response) {
            return;
        }
        return self::decode($ch, $response);
    }
    
    /**
     * Prepare cURL resource for GET request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @return resource cURL
     * @throws TwistException
     */
    public function curlGet($url, $params = array()) {
        return self::curlGetAction($url, $params, false);
    }
    
    /**
     * Prepare cURL resource for GET OAuth echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @return resource cURL
     * @throws TwistException
     */
    public function curlGetOut($url, $params = array()) {
        return self::curlGetAction($url, $params, true);
    }
    
    /**
     * Prepare cURL resource for streaming GET request.
     *
     * @param string $url endpoint URL
     * @param callable $callback function for processing each message
     * @param array|string [$params] 1-demensional array or query string
     * @return resource cURL
     * @throws TwistException
     */
    public function curlStreaming($url, $callback, $params = array()) {
        $url      = self::validateUrl($url);
        $obj      = self::getParamObject(self::validateParams($params));
        $callback = self::validateCallback($callback);
        $params   = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER     => $this->getAuthorization($url, 'GET', $params, 0),
            CURLOPT_URL            => $url . '?' . http_build_query($params, '', '&'),
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_WRITEFUNCTION  => function ($ch, $str) use ($callback) {
                static $first = true;
                static $buffer = '';
                $buffer .= $str;
                if (trim($buffer) === '') {
                    return strlen($str);
                }
                switch (true) {
                    case $first and strpos($buffer, '{') !== 0 || json_decode($buffer):
                        $first = false;
                    case $buffer[strlen($buffer) - 1] === "\n":
                        if ($callback(self::decode($ch, $buffer))) {
                            return 0;
                        }
                        $buffer = '';
                    default:
                        return strlen($str);
                }
            }
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPost($url, $params = array(), $wait_response = true) {
        return self::curlPostAction($url, $params, $wait_response, false);
    }
    
    /**
     * Prepare cURL resource for POST OAuth Echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostOut($url, $params = array(), $wait_response = true) {
        return self::curlPostAction($url, $params, $wait_response, true);
    }
    
    /**
     * Prepare cURL resource for POST request "oauth/request_token".
     *
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostRequestToken() {
        $url    = 'https://api.twitter.com/oauth/request_token';
        $params = array();
        $ch     = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $this->getAuthorization($url, 'POST', $params, self::MODE_REQUEST_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request "oauth/access_token".
     *
     * @param string $oauth_verifier
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostAccessToken($oauth_verifier) {
        $url    = 'https://api.twitter.com/oauth/access_token';
        $params = self::validateParams(compact('oauth_verifier'));
        $ch     = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $this->getAuthorization($url, 'POST', $params, self::MODE_ACCESS_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for multipart POST request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return resource cURL
     */
    public function curlPostMultipart($url, $params = array(), $wait_response = true) {
        return self::curlPostMultipartAction($url, $params, $wait_response, false);
    }
    
    /**
     * Prepare cURL resource for multipart POST OAuth Echo request.
     *
     * @param string $url endpoint URL
     * @param array|string [$params] 1-demensional array or query string
     * @param bool [$wait_response]
     * @return resource cURL
     */
    public function curlPostMultipartOut($url, $params = array(), $wait_response = true) {
        return self::curlPostMultipartAction($url, $params, $wait_response, true);
    }
    
    /**
     * Initialize cURL resource.
     * 
     * @return resource cURL
     */
    private static function curlInit() {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => 'gzip',
            CURLOPT_COOKIEJAR      => '',
        ));
        return $ch;
    }
    
    /**
     * Set cURL options for authenticity_token.
     *
     * @param resource $ch cURL 
     * @param TwistOAuth $to
     */
    private static function curlSetOptForAuthenticityToken($ch, $to) {
        curl_setopt($ch, CURLOPT_URL, $to->getAuthorizeUrl(true));
    }
    
    /**
     * Set cURL options for oauth_verifier.
     *
     * @param resource $ch cURL
     * @param TwistOAuth $to
     * @param string $authenticity_token
     * @param string $username
     * @param string $password
     */
    private static function curlSetOptForVerifier($ch, $to, $authenticity_token, $username, $password) {
        $params = array(
            'session[username_or_email]' => $username,
            'session[password]'          => $password,
            'authenticity_token'         => $authenticity_token,
        );
        curl_setopt_array($ch, array(
            CURLOPT_URL        => $to->getAuthorizeUrl(true),
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
    }
    
    /**
     * Execute multiple cURL requests actually.
     * 
     * @param array $curls
     *     e.g.
     *     array(
     *         'foo' => cURL resource of foo
     *         'bar' => cURL resource of bar
     *         'baz' => cURL resource of baz
     *         ...
     *     )
     * @param bool $is_streaming
     * @return array
     *     e.g.
     *     array(
     *         'foo' => stdClass or array,
     *         'bar' => stdClass or array,
     *         'baz' => stdClass or array,
     *         ...
     *     )
     * @throws TwistException
     */
    private static function curlMultiExecAction(array $curls, $is_streaming) {
        $mh = curl_multi_init();
        $chs = $responses = array();
        if (!$curls) {
            return array();
        }
        foreach ($curls as $i => $ch) {
            try {
                $chs[$i] = self::validateCurl($ch);
                curl_multi_add_handle($mh, $ch);
            } catch (TwistException $e) {
                throw new TwistException('(' . $i . ') ' . $e->getMessage(), $e->getCode());
            }
        }
        while (CURLM_CALL_MULTI_PERFORM === $stat = curl_multi_exec($mh, $running));
        if (!$running || $stat !== CURLM_OK) {
            throw new TwistException('Failed to start multiple requests.');
        }
        do {
            curl_multi_select($mh, 10);
            while (CURLM_CALL_MULTI_PERFORM === $stat = curl_multi_exec($mh, $running));
        } while ($running);
        if ($is_streaming) {
            throw new TwistException('Streaming stopped.');
        } else {
            foreach ($chs as $i => $ch) {
                try {
                    $responses[$i] = self::decode($ch, curl_multi_getcontent($ch));
                } catch (TwistException $e) {
                    throw new TwistException('(' . $i . ') ' . $e->getMessage(), $e->getCode());
                }
            }
            return $responses;
        }
    }
    
    /**
     * Force callable function.
     * 
     * @param mixed $callback
     * @return callable filtered callback
     */
    private static function validateCallback($callback) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback.');
        }
        return $callback;
    }
    
    /**
     * Force HTTP URL.
     * 
     * @param mixed $url
     * @return string filtered url
     */
    private static function validateUrl($url) {
        switch (true) {
            case false === $p = parse_url($url):
                throw new InvalidArgumentException('Invalid URL: parse failure.');
            case !isset($p['scheme']) || !preg_match('@^https?+://@i', $url):
                throw new InvalidArgumentException('Invalid URL: https:// or http:// scheme required.');
            case !isset($p['host']):
                throw new InvalidArgumentException('Invalid URL: host required.');
            case isset($p['query']):
                throw new InvalidArgumentException('Invalid URL: extra element: query');
            case isset($p['fragment']):
                throw new InvalidArgumentException('Invalid URL: extra element: fragment');
        }
        return sprintf(
            '%s://%s%s',
            $p['scheme'],
            $p['host'],
            isset($p['path']) ? $p['path'] : ''
        );
        return $url;
    }
    
    /**
     * Force GET or POST.
     * 
     * @param mixed $method
     * @return string filtered method
     */
    private static function validateMethod($method) {
        if (!strcasecmp('GET', $method) && !strcasecmp('POST', $method)) {
            throw new InvalidArgumentException('This library supports only GET or POST.');
        }
        return strtoupper($method);
    }
    
    /**
     * Force parameters 1-demensional array or query string.
     * 
     * @param mixed $params
     * @return array filterd parameters
     */
    private static function validateParams($params) {
        if (is_array($params)) {
            $params = array_map('filter_var', array_filter($params, function ($v) { return $v !== null; }));
            if (false !== $key = array_search(false, $params, true)) {
                throw new InvalidArgumentException('The parameter "' . $key . '" must be scalar.');
            }
            return $params;
        }
        if (false === $params = filter_var($params)) {
            throw new InvalidArgumentException('The parameters must be 1-demensional array or query string.');
        }
        $tmp = array();
        if ('' !== $params = trim($params)) {
            foreach (explode('&', $params) as $pair) {
                list($key, $value) = explode('=', $pair, 2) + array(1 => '');
                $tmp[urldecode($key)] = urldecode($value);
            }
        }
        return $tmp;
    }
    
    /**
     * Force valid cURL resource.
     * 
     * @param mixed $ch
     * @return resource cURL
     */
    private static function validateCurl($ch) {
        switch (true) {
            case !is_resource($ch):
            case stripos($type = get_resource_type($ch), 'curl') === false:
            case stripos($type, 'multi') !== false:
                throw new InvalidArgumentException(sprintf(
                    '(%s) The parameter must be valid cURL resource',
                    $i
                ));
        }
        return $ch;
    }

    
    /**
     * Force string.
     * 
     * @param string $key for exception message
     * @param mixed $value
     * @return string filtered string
     */
    private static function validateString($key, $value) {
        if (false === $value = filter_var($value)) {
            throw new InvalidArgumentException('The value for "' . $key . '" must be string.');
        }
        return $value;
    }
    
    /**
     * Safe file_get_contents().
     * 
     * @param string $key for exception message
     * @param string $value path
     */
    private static function safeGetContents($key, $value) {
        if (false === $value = @file_get_contents($value)) {
            throw new InvalidArgumentException('The file for "' . $key . '" not found.');
        }
        return $value;
    }
    
    /**
     * Solve parameters with prefix "@".
     * 
     * @param array $params valid parameters
     * @return stdClass an object contains "paramData", "paramIsFile"
     */
    private static function getParamObject(array $params) {
        $obj              = new stdClass;
        $obj->paramData   = array();
        $obj->paramIsFile = array();
        foreach ($params as $key => $value) {
            if (strpos($key, '@') === 0) {
                $obj->paramData[substr($key, 1)]   = self::safeGetContents($key, $value);
                $obj->paramIsFile[substr($key, 1)] = true;
            } else {
                $obj->paramData[$key]   = $value;
                $obj->paramIsFile[$key] = false;
            }
        }
        return $obj;
    }
    
    /**
     * Parse authenticity_token.
     * 
     * @param resource $ch cURL resource
     * @param string $response
     * @return string authenticity_token
     * @throws TwistException
     */
    private static function parseAuthenticityToken($ch, $response) {
        static $pattern = '@<input name="authenticity_token" type="hidden" value="([^"]++)" />@';
        if (!preg_match($pattern, $response, $matches)) {
            $info = curl_getinfo($ch);
            throw new TwistException('Failed to get authenticity_token.', $info['http_code']);
        }
        return $matches[1];
    }
    
    /**
     * Parse oauth_verifier.
     * 
     * @param resource $ch cURL resource
     * @param string $response
     * @return string oauth_verifier
     * @throws TwistException
     */
    private static function parseVerifier($ch, $response) {
        static $pattern = '@oauth_verifier=([^"]++)"|<code>([^<]++)</code>@';
        if (!preg_match($pattern, $response, $matches)) {
            $info = curl_getinfo($ch);
            throw new TwistException('Wrong username or password.', $info['http_code']);
        }
        return implode('', array_slice($matches, 1));
    }
    
    /**
     * Prepare headers for authorization.
     *
     * @param string $url endpoint URL
     * @param string $method GET or POST
     * @param array &$params 1-demensional array
     * @param int $flags self::MODE_*
     * @return array headers
     */
    private function getAuthorization($url, $method, &$params, $flags) {
        $oauth = array(
            'oauth_consumer_key'     => $this->ck,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0',
            'oauth_nonce'            => md5(mt_rand()),
            'oauth_token'            => $this->ot,
        );
        $key = array($this->cs, $this->os);
        if ($flags & self::MODE_REQUEST_TOKEN) {
            unset($oauth['oauth_token']);
            $key[1] = '';
        }
        if ($flags & self::MODE_ACCESS_TOKEN) {
            if (isset($params['oauth_verifier'])) {
                $oauth['oauth_verifier'] = $params['oauth_verifier'];
                unset($params['oauth_verifier']);
            }
        }
        $base = $oauth + $params;
        uksort($base, 'strnatcmp');
        $oauth['oauth_signature'] = base64_encode(hash_hmac(
            'sha1',
            implode('&', array_map('rawurlencode', array(
                $method,
                $url,
                str_replace(
                    array('+', '%7E'), 
                    array('%20', '~'), 
                    http_build_query($base, '', '&')
                ),
            ))),
            implode('&', array_map('rawurlencode', $key)),
            true
        ));
        return array(
            'Authorization: OAuth ' . http_build_query($oauth, '', ', ')
        );
    }
    
    /**
     * Prepare headers for OAuth Echo.
     *
     * @return array headers
     */
    private function getOAuthEcho() {
        $url     = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $params  = array();
        $headers = $this->getAuthorization($url, 'GET', $params, self::MODE_DEFAULT);
        return array(
            'X-Auth-Service-Provider: ' . $url,
            'X-Verify-Credentials-Authorization: OAuth realm="http://api.twitter.com/", ' . substr($headers[0], 21),
        );
    }
    
    /**
     * Prepare cURL resource for GET request actually.
     *
     * @param string $url endpoint URL
     * @param array|string $params 1-demensional array or query string
     * @param bool $out for OAuth Echo or not 
     * @return resource cURL
     * @throws TwistException
     */
    private function curlGetAction($url, $params, $out) {
        $url    = self::validateUrl($url);
        $obj    = self::getParamObject(self::validateParams($params));
        $params = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'GET', $params, 0),
            CURLOPT_URL        => $url . '?' . http_build_query($params, '', '&'),
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request actually.
     *
     * @param string $url endpoint URL
     * @param array|string $params 1-demensional array or query string
     * @param bool $wait_response
     * @param bool $out for OAuth Echo or not 
     * @return resource cURL
     * @throws TwistException
     */
    private function curlPostAction($url, $params, $wait_response, $out) {
        $url    = self::validateUrl($url);
        $obj    = self::getParamObject(self::validateParams($params));
        $params = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER     => $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'POST', $params, 0),
            CURLOPT_URL            => $url,
            CURLOPT_POSTFIELDS     => http_build_query($params, '', '&'),
            CURLOPT_POST           => true,
            CURLOPT_NOSIGNAL       => !$wait_response,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for GET request actually.
     *
     * @param string $url endpoint URL
     * @param array|string $params 1-demensional array or query string
     * @param bool $wait_response
     * @param bool $out for OAuth Echo or not 
     * @return resource cURL
     */
    private function curlPostMultipartAction($url, $params, $wait_response, $out) {
        static $disallow = array("\0", "\"", "\r", "\n");
        $url  = self::validateUrl($url);
        $obj  = self::getParamObject(self::validateParams($params));
        $body = array();
        foreach ($obj->paramData as $key => $value) {
            if ($obj->paramIsFile[$key]) {
                $body[] = implode("\r\n", array(
                    sprintf(
                        'Content-Disposition: form-data; name="%s"; filename="%s"',
                        str_replace($disallow, '_', $key),
                        md5(mt_rand() . microtime())
                    ),
                    'Content-Type: application/octet-stream',
                    '',
                    $value,
                ));
            } else {
                $body[] = implode("\r\n", array(
                    sprintf(
                        'Content-Disposition: form-data; name="%s"',
                        str_replace($disallow, '_', $key)
                    ),
                    '',
                    $value,
                ));
            }
        }
        do {
            $boundary = '---------------------' . md5(mt_rand() . microtime());
        } while (preg_grep('/' . $boundary . '/', $body));
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });
        $body[] = '--' . $boundary . '--';
        $body[] = '';
        $params = array();
        $ch = self::curlInit();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER     => array_merge(
                $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'POST', $params, 0),
                array('Content-Type: multipart/form-data; boundary=' . $boundary)
            ),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_POST       => true,
            CURLOPT_NOSIGNAL   => !$wait_response,
        ));
        return $ch;
    }
    
}

/**
 * Image class.
 * Instances are internally genereted.
 */
final class TwistImage {
    
    /**
     * @property-read string $contentType
     * @property-read string $binaryData
     */
    private $contentType;
    private $binaryData;
    
    /**
     * A flag for reinitialize prevention.
     * 
     * @property bool $constructed
     */
    private $constructed = false;
    
    /**
     * Constructor.
     * 
     * @param string $content_type
     * @param string $binary_data
     */
    public function __construct($content_type, $binary_data) {
        if ($this->constructed) {
            throw new BadMethodCallExceptpion('Do not call __construct() by yourself.');
        }
        $this->contentType = filter_var($content_type);
        $this->binaryData = filter_var($binary_data);
    }
    
    /**
     * Getter for private properties.
     *
     * @name string property name
     * @return mixed
     */
    public function __get($name) {
        $name = filter_var($name);
        if (!property_exists($this, $name)) {
            throw new OutOfRangeException('Invalid property: ' . $name);
        }
        return $this->$name;
    }
    
    /**
     * Make format of Data URI.
     *
     * @name string property name
     * @return string
     */
    public function getDataUri() {
        return sprintf('data:%s;base64,%s', $this->contentType, base64_encode($this->binaryData));
    }
    
}

/**
 * Exception related to Twitter.
 */
final class TwistException extends RuntimeException { }