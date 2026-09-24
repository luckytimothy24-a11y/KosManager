<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Kos;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\Kontrak;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\SharedBillingService;
use Carbon\Carbon;
use Illuminate\Support\Str;

$kos = Kos::find(4);
$kamar = Kamar::find(16);
if (!$kos || !$kamar) { echo "Kos/Kamar not found\n"; exit(1); }
echo "Kos #{$kos->id} {$kos->name} | Kamar {$kamar->room_number} cap={$kamar->capacity} mode={$kamar->billing_mode} price=".number_format($kamar->monthly_price,0,',','.')."\n";

$existing = Penghuni::where('kamar_id',$kamar->id)->where('status','active')->count();
if ($existing >= 4) {
    echo "Already {$existing} penghuni, cleaning...\n";
    // soft delete existing for demo idempotency? keep
}

$names = ['Andi Patungan','Budi Shared','Citra Kos','Dedi Bersama'];
$tenants = [];
foreach ($names as $i => $name) {
    $email = strtolower(str_replace(' ','.', $name)).".demo".($kos->id)."@example.com";
    $user = User::where('email',$email)->first();
    if (!$user) {
        $user = User::factory()->create(['name'=>$name,'email'=>$email,'role'=>'tenant']);
        echo "Created tenant {$user->id} {$name} {$email}\n";
    } else {
        echo "Reuse tenant {$user->id} {$name}\n";
    }
    $tenants[] = $user;
    $ph = Penghuni::where('user_id',$user->id)->where('kamar_id',$kamar->id)->where('status','active')->first();
    if (!$ph) {
        $ph = Penghuni::create([
            'user_id'=>$user->id,
            'kos_id'=>$kos->id,
            'kamar_id'=>$kamar->id,
            'identity_number'=>'3201'.str_pad($user->id,12,'0',STR_PAD_LEFT),
            'phone'=>'0812'.rand(10000000,99999999),
            'address'=>'Jl. Demo No '.($i+1),
            'check_in_date'=>Carbon::now()->subDays(5),
            'status'=>'active',
        ]);
        echo "  Penghuni {$ph->id} user {$user->id}\n";
    }
    $kt = Kontrak::where('penghuni_id',$ph->id)->where('status','active')->first();
    if (!$kt) {
        $kt = Kontrak::create([
            'contract_number'=>'KT-'.strtoupper(Str::random(12)),
            'penghuni_id'=>$ph->id,
            'kos_id'=>$kos->id,
            'kamar_id'=>$kamar->id,
            'rental_type'=>'monthly',
            'rental_price'=>$kamar->monthly_price,
            'billing_mode'=>'shared',
            'start_date'=>Carbon::now()->subDays(5)->toDateString(),
            'end_date'=>Carbon::now()->addMonths(11)->toDateString(),
            'status'=>'active',
        ]);
        echo "  Kontrak {$kt->id} {$kt->contract_number} shared\n";
    }
}

$kamar->update(['status'=>'occupied']);
echo "Occupancy: ".$kamar->occupancy()."/{$kamar->capacity}\n";

$svc = app(SharedBillingService::class);
$periodStart = Carbon::now()->startOfMonth()->toDateString();
$periodEnd = Carbon::now()->endOfMonth()->toDateString();
$dueDate = Carbon::now()->addDays(7)->toDateString();
echo "Creating tagihan shared {$periodStart} s/d {$periodEnd} due {$dueDate}\n";

$created = $svc->createSharedTagihans($kamar->fresh(), 'sewa', $periodStart, $periodEnd, $dueDate, 0, 0);
if (empty($created)) {
    echo "No new tagihan (maybe duplicate). Listing existing:\n";
    $list = Tagihan::where('kamar_id',$kamar->id)->where('period_start',$periodStart)->where('period_end',$periodEnd)->get();
    foreach ($list as $t) echo "  TB {$t->bill_number} penghuni={$t->penghuni_id} total=".number_format($t->total,0,',','.')." room_bill=".number_format($t->total_room_bill,0,',','.')."\n";
} else {
    echo "Created ".count($created)." tagihan:\n";
    $sum = 0;
    foreach ($created as $t) {
        $t->load('penghuni.user');
        echo "  {$t->bill_number} {$t->penghuni->user->name} subtotal=".number_format($t->subtotal,0,',','.')." total=".number_format($t->total,0,',','.')." room_bill=".number_format($t->total_room_bill,0,',','.')."\n";
        $sum += (float)$t->total;
    }
    echo "  SUM=".number_format($sum,0,',','.')." room_bill=".number_format($created[0]->total_room_bill,0,',','.')." balance=".($sum == $created[0]->total_room_bill ? 'OK':'MISMATCH')."\n";
}

// Contoh pembayaran 1 penghuni (Andi) via transfer_bank
$firstTagihan = Tagihan::where('kamar_id',$kamar->id)->where('period_start',$periodStart)->first();
if ($firstTagihan) {
    $existsPay = Pembayaran::where('tagihan_id',$firstTagihan->id)->exists();
    if (!$existsPay) {
        $pay = Pembayaran::create([
            'payment_number'=>'PY-'.strtoupper(Str::random(12)),
            'tagihan_id'=>$firstTagihan->id,
            'penghuni_id'=>$firstTagihan->penghuni_id,
            'amount'=>$firstTagihan->total,
            'payment_date'=>now(),
            'payment_method'=>'transfer_bank',
            'proof_file'=>null,
            'verification_status'=>'pending',
            'active_payment_key'=>$firstTagihan->id,
        ]);
        $firstTagihan->update(['status'=>'pending_verification']);
        echo "Pembayaran demo {$pay->payment_number} for {$firstTagihan->bill_number} amount=".number_format($pay->amount,0,',','.')." pending\n";
    } else {
        echo "Pembayaran for {$firstTagihan->bill_number} already exists\n";
    }
}

echo "DONE — Cek owner/tagihan & tenant/tagihan\n";
