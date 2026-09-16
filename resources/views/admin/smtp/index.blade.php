@php
    $defaultTab = 'smtp';
    if (!isset($googleClientId)) {
        $unifiedData = \App\Http\Controllers\Admin\AdminSettingController::getUnifiedSettingData();
        foreach ($unifiedData as $k => $v) {
            if (!isset($$k)) {
                $$k = $v;
            }
        }
    }
@endphp
@include('admin.settings.index', array_merge(get_defined_vars(), ['defaultTab' => 'smtp']))
