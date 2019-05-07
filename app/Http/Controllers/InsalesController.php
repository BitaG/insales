<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request,
    Illuminate\Support\Facades\Log;
use App\Shop,
    App\Meta;

class InsalesController extends Controller
{
    //install shop
    public function install( Request $request)
    {
        $shop_id    = $request->insales_id;//shop_id

        if($shop_id == null) {
            Log::error('InSales | Install: dont have shop_id in request');
            return response()->json('Forbidden', 400);
        }

        $shop = Shop::where('shop_id',$shop_id)->value('id');

        if ($shop !== null) {
            Log::error('InSales | Install: Already installed.');
            return response()->json('Already installed.', 400);
        }

        //add shop in DB
        $new_shop = new Shop();
        $new_shop->shop_id = $shop_id; // shop_id
        $new_shop->password = md5($request->token.env("INSALES_APP_SECRET") ); // password
        $new_shop->shop = $request->shop; // shop
        $new_shop->save();

        //add meta from shop
        $new_meta = new Meta();
        $new_meta->shop_id = $shop_id;
        $new_meta->save();

        return response()->json("ОК", 201);
    }

    //uninstall shop
    public function uninstall(Request $request)
    {
        $shop_id    = $request->insales_id;//shop_id

        if($shop_id == null) {
            Log::error('InSales | Uninstall: dont have shop_id in request');
            return response()->json('Bad request', 400);
        }

        $shop = Shop::where('shop_id', $shop_id)->first();
        $meta = Meta::where('shop_id', $shop_id)->first();

        //todo delete webhook from inSales

        $shop->delete();
        $meta->delete();

        Log::info('InSales | Uninstall: delete shop '.$shop_id);
        return response()->json("ОК", 200);
    }

    //login
    public function login(Request $request)
    {
        $sessionShopId  = $request->session()->get('shopId');
        $cookieShopId   = $request->cookie('shopId');
        $shopId         = $request->insales_id;
        if (empty($shopId)) {
            Log::error('InSales | Login: dont have shop');
            return response()->json('Bad request', 400);
        }

        $shop           = Shop::where('shop_id', $shopId)->first();

        if ( $shop === null ) {
            Log::error('InSales | Login: dont have shop');
            return response()->json('Dont find shop.', 400);
        }

        Cookie::queue('InSales','https://'.$shop->shop.'/admin2/dashboard', 60);


//        $insale_client  = new InsaleClient();

        if ( !empty($sessionShopId) && !empty($cookieShopId)) {
            if ($sessionShopId === $cookieShopId) {
                return redirect()->route('app');
            }
        }

        $token = md5(microtime().md5(microtime()));

        $url = sprintf('http://%s/admin/applications/%s/login?token=%s&login=%s/autologin',
            $request->shop,
            env("INSALES_APP_NAME"),
            $token,
            url('/')
        );

        $request->session()->put('token', $token);
        $request->session()->put('shopId', $shopId);

        return redirect($url);
    }

    //autologin
    public function autologin(Request $request)
    {
        $token3         = $request->token3;
        $shopId         = $request->session()->get('shopId');
        $password       = Shop::where('shop_id', $shopId)->value('password');

        //collect string
        $string =
            $request->session()->get('token').
            $request->user_email.
            $request->user_name.
            $request->user_id.
            $request->email_confirmed.
            $password;

        if ($token3 == md5($string)) {
            Cookie::queue('shopId', $shopId, 60);

            return redirect()->route('export');
        }

        Log::error('InSales | Auto login: dont have token3');

        //todo add page error from bad login request
        return redirect()->route('error', ['code' => '401']);
    }

}
