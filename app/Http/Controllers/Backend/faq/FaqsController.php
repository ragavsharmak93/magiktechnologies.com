<?php

namespace App\Http\Controllers\Backend\faq;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\FaqLocalization;
use App\Models\GeneralSetupLocalization;

class FaqsController extends Controller
{
    # construct
    public function __construct()
    {
        $this->middleware(['permission:faqs'])->only([
            'index','store',"edit","update","delete"
        ]);
    }

    # get all faqs
    public function index(Request $request)
    {
        $searchKey = null;
        $faqs = Faq::oldest();
        if ($request->search != null) {
            $faqs = $faqs->where('question', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        $faqs = $faqs->paginate(paginationNumber());
        return view('backend.pages.faqs.index', compact('faqs', 'searchKey'));
    }

    # faq store
    public function store(Request $request)
    {
        $faq = new Faq;
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->save();

        flash(localize('FAQ has been added successfully'))->success();
        return redirect()->route('admin.faqs.index');
    }

    # edit faq
    public function edit(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);
        $lang_key = $request->lang_key ?? env('DEFAULT_LANGUAGE');
        return view('backend.pages.faqs.edit', compact('faq', 'lang_key'));
    }

    # update Faq
    public function update(Request $request)
    {
        if (checkLanguage($request->language_key)) {
            $faq = Faq::findOrFail($request->id);
            $faq->question = $request->question;
            $faq->answer = $request->answer;
            $faq->save();
        }
        if ($request->filled('language_key')) {
            $this->storeLocalizationData($request);
        }
        flash(localize('Faq has been updated successfully'))->success();
        return back();
    }

    # delete Faq
    public function delete($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();
        flash(localize('Faq has been deleted successfully'))->success();
        return back();
    }
    private function storeLocalizationData($request)
    {
        $lang_key = $request->language_key ?? App::getLocale();

        FaqLocalization::updateOrCreate([
            'lang_key' => $lang_key,
            'faq_id' => $request->id
        ], [
            'question' => $request->question,
            'answer' => $request->answer
        ]);
    }
}
