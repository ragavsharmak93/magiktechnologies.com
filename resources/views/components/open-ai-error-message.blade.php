@if(openAiErrorMessage() != null)
<div class="col-lg-12">
    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
        {{localize(openAiErrorMessage())}}                          
    </div>
</div>
@endif