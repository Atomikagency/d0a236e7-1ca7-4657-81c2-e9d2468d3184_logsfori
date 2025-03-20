
<style>
    body {
        background:white
    }
    input:focus {
        border:none !important;
        box-shadow: none !important;
    }
</style>
<div class="wrap h-full">
    <h1>LogsForI</h1>
    <div>
        <div class="px-4 sm:px-0">
            <h3 class="text-base/7 font-semibold text-gray-900 !mb-0">Application Settings</h3>
            <p class="!mt-0 max-w-2xl text-sm/6 text-gray-500">Link your application with LogsForI Connection</p>
        </div>
    </div>
    <?php
    $status = get_option('logsfori_connection_status');
    if(!empty($status)){
        switch($status){
            case 'success':
                ?>
                <div class=" border-l-4 border-green-400  bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <div class="mt-2 text-sm text-green-700">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    <li>Congrats! You are correctly connected to logsForI</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                break;
            case 'failed':
                ?>
                <div class=" border-l-4 border-red-400  bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <div class="mt-2 text-sm text-red-700">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    <li>Your connection was failed. Please check if token is correct otherwise contact logsForI support.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                break;
        }
    }
    ?>

    <form method="POST" action="options.php">
        <?php settings_fields('logsfori_option_group'); ?>
        <div class="space-y-12">
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="token" class="block text-sm/6 font-medium text-gray-900">Api token</label>
                        <div class="mt-2">
                            <div class="!flex !items-center !rounded-md !bg-white !outline !outline-1  !-outline-offset-1 !outline-gray-300 !focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                <input value="<?php echo get_option('logsfori_token'); ?>" type="password" name="logsfori_token" id="token" class="block min-w-0 !border-0 grow !py-1.5 !pl-1 !pr-3 !text-base !text-gray-900 !placeholder:text-gray-400 focus:!border-none !focus:shadow-none !focus:outline !focus:outline-0 sm:text-sm/6">
                            </div>
                            <small>You can find it by connecting to LogsForI and create a new connection</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-y-12">
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label class="block text-sm/6 font-medium text-gray-900">Enable Load Time Analysis</label>
                        <?php $enabled = get_option('logsfori_enable_timer', false); ?>
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" id="logsfori_enable_timer" name="logsfori_enable_timer" value="1" <?php checked($enabled, 1); ?>
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label   for="logsfori_enable_timer" class="ml-2 text-sm text-gray-600">Track and log page load times</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-12">
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="token" class="block text-sm/6 font-medium text-gray-900">Severity Settings</label>
                        <?php
                        $saved_severity = get_option('logsfori_severity_min', 'info');
                        $severities = logsfori_get_severity_levels();
                        ?>
                        <div class="mt-2 !w-full">
                            <select style="max-width:100% !important;height:37px" name="logsfori_severity_min"
                                    class="col-start-1 row-start-1 !w-full appearance-none rounded-md bg-white !py-1.5 !pl-3 !pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <?php
                                foreach ($severities as $key => $label) {
                                    echo '<option value="' . esc_attr($label) . '" ' . selected($saved_severity, $label, false) . '>' . esc_html(ucfirst($label)) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <small>Warning : Change this value only if you know what you do. You can lost event.</small>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="submit" name="general_settings" class=" cursor-pointer rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
<?php