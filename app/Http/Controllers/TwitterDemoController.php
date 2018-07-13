<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuthException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;
use Webpatser\Uuid\Uuid;


class TwitterDemoController extends Controller
{

    public function upload(Request $request)
    {

        $uuid = Uuid::generate(4);

        $this->validate($request,[
            'uploadfile' => 'image|mimes:jpeg,jpg'
        ]);

        if (! $request->file('photo')->isValid()) {
            abort(401, '');
        }
        $file = $request->file('uploadfile');
        $extension = $file->getClientOriginalExtension();
        $filename = $uuid . '.' . $extension;
        $file->move('/tmp/', $filename);
        $file_path = '/tmp/' . $filename;

        $twitter = new TwitterOAuth(
            config('twitter.consumer_key'),
            config('twitter.consumer_secret')
        );
        try {
            # 認証用のrequest_tokenを取得
            # このとき認証後、遷移する画面のURLを渡す
            $token = $twitter->oauth('oauth/request_token', array(
                'oauth_callback' => config('twitter.callback_url')
            ));
        } catch (TwitterOAuthException $e) {
            Log::error($e);
            abort(500, 'twitter のアクセストークン取得に失敗しました');
        }

        # 認証画面で認証を行うためSessionに入れる
        session(array(
            'oauth_token' => $token['oauth_token'],
            'oauth_token_secret' => $token['oauth_token_secret'],
            'image_file_path' => $file_path,
        ));

        # 認証画面へ移動させる
        ## 毎回認証をさせたい場合： 'oauth/authorize'
        ## 再認証が不要な場合： 'oauth/authenticate'
        $url = $twitter->url('oauth/authenticate', array(
            'oauth_token' => $token['oauth_token']
        ));

        return redirect($url);
    }


    public function callback(Request $request)
    {
        $oauth_token = session('oauth_token');
        $oauth_token_secret = session('oauth_token_secret');

        # request_tokenが不正な値だった場合エラー
        if ($request->has('oauth_token') && $oauth_token !== $request->oauth_token) {
            return Redirect::to('/login');
        }

        # request_tokenからaccess_tokenを取得
        $twitter = new TwitterOAuth(
            $oauth_token,
            $oauth_token_secret
        );
        try {
            $token = $twitter->oauth('oauth/access_token', array(
                'oauth_verifier' => $request->oauth_verifier,
                'oauth_token' => $request->oauth_token,
            ));
        } catch (TwitterOAuthException $e) {
            Log::error($e);
            abort(500, 'twitter のアクセストークン取得に失敗しました');
        }
        # access_tokenを用いればユーザー情報へアクセスできるため、それを用いてTwitterOAuthをinstance化
        $twitter_user = new TwitterOAuth(
            config('twitter.consumer_key'),
            config('twitter.consumer_secret'),
            $token['oauth_token'],
            $token['oauth_token_secret']
        );

        # 本来はアカウント有効状態を確認するためのものですが、プロフィール取得にも使用可能
        # $twitter_user_info = $twitter_user->get('account/verify_credentials');
        # dd($twitter_user_info);

        // 画像をアップロード
        $media = $twitter_user->upload('media/upload', ['media' => './image.jpg']);
        // ツイートの内容を設定
        $params = [
            'status' => 'test #test',
            'media_ids' => implode(',', [$media->media_id_string])
        ];

        // ツイートする
        $result = $twitter_user->post('statuses/update', $params);

        // return '投稿しました。';
        return redirect('http://takemitsu.net');
    }
}
