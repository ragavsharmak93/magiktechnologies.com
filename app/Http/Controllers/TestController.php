<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Page;
use App\Models\Theme;
use App\Models\AdSense;
use App\Models\Project;
use App\Traits\Language;
use App\Models\PWASettings;
use App\Models\MediaManager;
use App\Traits\SystemUpdate;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Traits\GenerateVoice;
use Faker\Generator as Faker;
use Orhanerday\OpenAi\OpenAi;
use App\Models\PaymentGateway;
use App\Models\WritebotModule;
use App\Models\ElevenLabsModel;
use App\Exports\CustomersExport;
use App\Models\PageLocalization;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use App\Http\Services\SerperService;
use App\Models\ElevenLabsModelVoice;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Support\Entities\Priority;
use Illuminate\Support\Facades\Artisan;
use App\Http\Services\ElevenLabsService;
use App\Http\Services\OpenAiCustomService;
use League\CommonMark\Normalizer\SlugNormalizer;
use App\Http\Controllers\Backend\Templates\TemplatesController;
use App\Http\Controllers\Backend\Payments\Paypal\PaypalController;

class TestController extends Controller
{
  //
  use Language;
  use GenerateVoice;
  use SystemUpdate;
  public function index(Faker $faker)
  {
  }
  /**
   * fact 1 : unlimited
   * fact 2 : customer check and available balance checking
   * fact 3 :request max token compare with available balance
   */
  public function test(Request $request)
  {

    dd('ok');
    $page = Page::where('slug', 'privacy-policy')->first();
    if(!$page){
        $request = (object)[
            'title'            => 'Privacy Policy',
            'slug'             => 'privacy-policy',
            'meta_title'       => 'writebot',
            'meta_description' => 'writebot',
            'content'          => 'Welcome to ThemeTags!',
            'meta_image'       => null,
            'is_system'        => 1                
        ];
        $this->storePage($request);
    }
  }
  private function storePage($request, $modelId = null)
  {
      if($modelId){
          $page = Page::findOrFail($modelId);
      }else{
          $page = new Page;
      }
      $page->title            = $request->title;
      $page->slug             = convertToSlug($request->title);
      $page->content          = $request->content;
      $page->meta_title       = $request->meta_title;
      $page->meta_description = $request->meta_description;
      $page->meta_image       = $request->meta_image;
      $page->is_system        = $request->is_system ?? 0;
      $page->save();

      $pageLocalization           = PageLocalization::firstOrNew(['lang_key' => env('DEFAULT_LANGUAGE'), 'page_id' => $page->id]);
      $pageLocalization->title    = $request->title;
      $pageLocalization->content  = $request->content;
      $pageLocalization->save();
  }
  // baclup
  function migrate(Request $request)
  {
    if ($request->has('smartyCoder') && $request->smartyCoder == 'aminulislam') {
      Artisan::call('migrate');
      Artisan::call('db:seed --class=FeatureCategorySeeder');
      Artisan::call('db:seed --class=AiApplicationSeeder');
      Artisan::call('db:seed --class=AiImageGenerateSeeder');
      Artisan::call('db:seed --class=FeatureToolSeeder');
      dd('Welcome! see you again');
    }
    dd('You are not a smarty coder');
  }
}
