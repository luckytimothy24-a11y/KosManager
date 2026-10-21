<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

function testGet($userId, $path)
{
    $user = User::find($userId);
    if (! $user) {
        echo "$path: no user $userId\n";

        return;
    }
    $req = Request::create($path, 'GET');
    $req->headers->set('Accept', 'text/html');
    // set authenticated user via session guard
    auth()->setUser($user);
    // also set request user resolver
    $req->setUserResolver(fn () => $user);
    // Use kernel handle
    global $app;
    $kernel = $app->make(Kernel::class);
    // Need to go through middleware: use http kernel
    try {
        $response = $kernel->handle($req);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $snippet = $status >= 400 ? substr(strip_tags($content), 0, 800) : 'OK '.strlen($content).' bytes';
        echo "$path as {$user->role}#$userId => $status ".($status >= 400 ? $snippet : 'OK')."\n";
        if ($status >= 500) {
            // also log exception
            if (preg_match('/Route \[([^\]]+)\] not defined/', $content, $m)) {
                echo "  Missing route: {$m[1]}\n";
            }
        }
        $kernel->terminate($req, $response);
    } catch (Throwable $e) {
        echo "$path EXCEPTION ".get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine()."\n";
    }
    auth()->logout();
}
// tenant user id 4 from earlier
testGet(4, '/dashboard');
testGet(4, '/tenant/checkin');
testGet(1, '/dashboard');
testGet(3, '/dashboard');
testGet(2, '/dashboard');
