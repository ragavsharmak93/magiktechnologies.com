<?php

namespace App\Http\Controllers\Backend\Settings;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplateStoreRequestForm;
use League\CommonMark\Normalizer\SlugNormalizer;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::all();
        return view('backend.pages.systemSettings.emailTemplate.index', compact('templates'));
    }
    public function update(EmailTemplateStoreRequestForm $request)
    {
        try {
            $requestData = $request->template;
            if(!empty($requestData)) {

                $template = EmailTemplate::where('id', $requestData['id'])->first();
                if(!$template){
                    $template = new EmailTemplate();
                    $template->created_by = auth()->user()->id;
                }
                $template->subject = $requestData['subject'];
                $template->code = $requestData['code'];
                $template->is_active = isset($requestData['is_active'])  ? 1 : 0;
                $template->updated_by = auth()->user()->id;
                $template->save();
                flash(localize('Email Template Update Successfully'))->success();
                return redirect()->route('admin.email-template.index');
            }
            flash(localize('Request data not found'))->error();
            return redirect()->route('admin.email-template.index');
        } catch (\Throwable $th) {
            flash($th->getMessage());
            return redirect()->back();
        }

    }
}
