<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Cart;
use Stripe\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use Stripe\PaymentIntent;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\StudentRegisteredNotification;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();


        Stripe::setApiKey(config('services.stripe.secret'));
        if (empty($user->stripe_customer_id)) {
            $customer = Customer::create([
                'email' => $user->email,
                'name'  => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }


        $cart = Cart::with('course')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $total = $cart->sum(fn($i) => (float) $i->course->price);



        $order = DB::transaction(function () use ($user, $cart, $total) {
            $order = Order::create([
                'user_id'      => $user->id,
                'total_amount' => $total,
                'status'       => 'pending',
            ]);
            foreach ($cart as $row) {
                OrderItem::create([
                    'order_id'  => $order->id,
                    'course_id' => $row->course_id,
                    'price'     => $row->course->price,
                ]);
            }
            return $order;
        });


        $pi = PaymentIntent::create([
            'amount'                     => (int) round($order->total_amount * 100),
            'currency'                   => strtolower('usd'), // أو عملتك
            'customer'                   => $user->stripe_customer_id,
            'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            'metadata'                   => ['order_id' => (string)$order->id],
        ]);


        Payment::create([
            'user_id'                 => $user->id,
            'stripe_payment_intent_id' => $pi->id,
            'status'                  => $pi->status ?? 'pending',
            'amount'                  => $order->total_amount,
            'currency'                => strtolower($order->currency ?? 'usd'),
            'response_payload'        => json_encode($pi->toArray()),
        ]);


        Cart::where('user_id', $user->id)->delete();

        return ApiResponse::sendResponse(201, 'Checkout completed successfully', [
            'order_id' => $order->id,
            'total'    => $order->total_amount,
            'currency' => $order->currency ?? 'usd',
            'payment_intent' => $pi->id,
            'client_secret'  => $pi->client_secret,
            'status'         => $pi->status,
        ]);
    }

    public function confirmWithSavedPM(Request $request)
    {
        $data = $request->validate([
            'order_id'          => 'required|exists:orders,id',
            'payment_method_id' => 'required|string', // pm_xxx
        ]);

        $user  = $request->user();
        $order = Order::findOrFail($data['order_id']);

        Stripe::setApiKey(config('services.stripe.secret'));


        $payment = Payment::where('user_id', $user->id)
            ->where('amount', $order->total_amount)
            ->latest()->first();

        if (!$payment) {
            return response()->json(['message' => 'PaymentIntent not found'], 404);
        }


        $pi = PaymentIntent::update($payment->stripe_payment_intent_id, [
            'payment_method' => $data['payment_method_id'],
        ]);
        $pi = PaymentIntent::retrieve($payment->stripe_payment_intent_id);
        $pi->confirm();

        $payment->update(['status' => $pi->status, 'response_payload' => $pi->toArray()]);
        $order->update(['status' => $pi->status]);

        $orderItems = $order->Items()->with('course.user')->get();

        foreach ($orderItems as $item) {
            $course   = $item->course;
            $instructor = $course->user;

            if ($instructor) {
                $instructor->notify(new StudentRegisteredNotification([
                    'student_name'  => $user->name,
                    'student_id'    => $user->id,
                    'course_title'  => $course->title,
                    'course_id'     => $course->id,
                ]));
            }
        }
        return ApiResponse::sendResponse(200 , 'Payment Intent Confirmed Successfully');
    }
}
