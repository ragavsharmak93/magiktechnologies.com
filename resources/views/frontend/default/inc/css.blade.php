@if (@$localLang->is_rtl == 1)
<link href="{{ staticAsset('backend/assets/css/main-rtl.css') }}" rel="stylesheet" type="text/css" />
@else
<link href="{{ staticAsset('backend/assets/css/main.css') }}" rel="stylesheet" type="text/css" />
@endif

