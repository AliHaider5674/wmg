<?php
use Illuminate\Database\Migrations\Migration;
use App\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;

/**
 * Create first init API user
 *
 * Class AddInitApiUser
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AddInitApiUser extends Migration
{
    const DEFAULT_USER_EMAIL = 'ci@athena.wmgecomstage.com';
    const DEFAULT_USER_NAME = 'Fulfillment';
    const DEFAULT_USER_PASSWORD = 'f9e*7-&xUg4(P3YP';
    const DEFAULT_OAUTH_CLIENT_ID = '5012';
    const DEFAULT_OAUTH_CLIENT_NAME = 'Fulfillment';
    const DEFAULT_OAUTH_CLIENT_SECRET = 'lMyHxPYF4XOSPaIx1sYsYtQeTVbFoH81eETCU1Oc';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = new User();
        $user->fill([
            'email' => env('INIT_USER_EMAIL', self::DEFAULT_USER_EMAIL),
            'password' => Hash::make(env('INIT_USER_PASSWORD', self::DEFAULT_USER_PASSWORD)),
            'name' => env('INIT_USER_NAME', self::DEFAULT_USER_NAME)
        ]);
        $user->save();

        $client = new Client();
        $client->fill([
            'id' => env('INIT_OAUTH_CLIENT_ID', self::DEFAULT_OAUTH_CLIENT_ID),
            'name' => env('INIT_OAUTH_CLIENT_NAME', self::DEFAULT_OAUTH_CLIENT_NAME),
            'redirect' => 'http://localhost',
            'password_client' => 1,
            'personal_access_client' => 0 ,
            'revoked' => 0,
            'secret' => env('INIT_OAUTH_CLIENT_SECRET', self::DEFAULT_OAUTH_CLIENT_SECRET)
        ]);
        $client->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = User::where('email', '=', env('INIT_USER_EMAIL', self::DEFAULT_USER_EMAIL))->get();
        foreach ($users as $user) {
            $user->delete();
        }

        $client = Client::where('id', '=', env('INIT_OAUTH_CLIENT_ID', self::DEFAULT_OAUTH_CLIENT_ID))->first();
        if ($client) {
            $client->delete();
        }
    }
}
