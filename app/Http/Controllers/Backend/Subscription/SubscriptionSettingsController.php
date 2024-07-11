<?php

namespace App\Http\Controllers\Backend\Subscription;

use App\Http\Controllers\Backend\Payments\Paypal\PaypalController;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayProduct;
use App\Models\SubscriptionPackage;

class SubscriptionSettingsController extends Controller
{
    //
    public function index()
    {
        $data = [];
        $isActivePaypal = paymentGateway('paypal')->is_active;
        $data['isActivePaypal'] = $isActivePaypal; 
        $exitProductIds = PaymentGatewayProduct::where('is_active', 1)->pluck('package_id')->toArray();
        $data['gateWaysProducts'] = PaymentGatewayProduct::get();
        $data['packages'] = SubscriptionPackage::whereNotIn('id', $exitProductIds)->where('package_type', '!=', 'starter')->get(['id', 'title', 'package_type', 'price']);
        return view('backend.pages.subscribers.settings', $data);
    }
    //
    public function store(Request $request)
    {

        try {
            SystemSetting::updateOrCreate([
                'entity' => $request->type
            ], [
                'value' => $request->is_active
            ]);
            cacheClear();           
            
            return 1;
        } catch (\Throwable $th) {
            return 0;
        }
    }
    public function storeGatewayproduct(Request $request)
    {
        try{
            if($request->packages) {
                foreach($request->packages as $package_id) {
             
                    $exitProduct = PaymentGatewayProduct::where('package_id', $package_id)->where('is_active', 1)->where('gateway', 'paypal')->first();
                    if(!$exitProduct) {
                        PaypalController::createProduct($package_id);
                    }                    
                }
            }
          
            flash(localize('Gateway Subscription product Created successfully'))->success();
            return redirect()->route('admin.subscription-settings.index');
        }catch(\Exception $e){
            flash(localize('Gateway Subscription product Created failed'))->error();
          return redirect()->back();
        }
    }
    public function view($id)
    {
        $product = PaymentGatewayProduct::whereNotNull('billing_id')->where('is_active', 1)->where('id', $id)->first();
        $details = null;
        if($product){
            $details = PaypalController::showPlanDetails($product->billing_id);
        }
      
        return view('backend.pages.gatewayPlan.paypal.paypalPlanDetails', compact('details'));
           
    }
    public function delete($id)
    {
        if(auth()->user()->user_type != 'admin') {
            abort(403);
        }
        $product = PaymentGatewayProduct::where('id',$id)->first();
       if(!$product){
            flash(localize('Gateway Subscription product delete failed'))->error();
            return redirect()->route('admin.subscription-settings.index');
       }
       if($product->billing_id) {
           PaypalController::deactivatePlan($product->billing_id);
       }
       $product->delete();
       flash(localize('Gateway Subscription product deleted successfully and DeActive Plan from Paypal'))->success();
       return redirect()->route('admin.subscription-settings.index');
           
    }
    private function getPaypalProductList()
    {
        return PaypalController::listProducts();       
    }
}
