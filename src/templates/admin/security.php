<style>
    body {
        background: white
    }

    input:focus {
        border: none !important;
        box-shadow: none !important;
    }
</style>
<div class="wrap h-full">
    <h1>LogsForI</h1>
    <form method="POST" action="">
        <div class="space-y-12">
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="token" class="block text-sm/6 font-medium text-gray-900">Apply default settings</label>
                        <p>Let LogsForI setup all necessary hook to deliver a great value for your application</p>
                        <button type="submit" name="logsfori_apply_default_settings"
                                class="cursor-pointer rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Trust LogsForI and apply best settings
                        </button>
                        <br>
                        <small>This action doesn't change anything on your wordpress. It will only listen hooks execution. You might change values in
                            Hooks event page after.</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div>
        <div class="px-4 sm:px-0">
            <h3 class="text-base/7 font-semibold text-gray-900 !mb-0">Hooks event</h3>
            <p class="!mt-0 max-w-2xl text-sm/6 text-gray-500">Manage which hook will be listened and let logsForI do the rest</p>
        </div>
    </div>
    <form method="POST" action="">
        <div class="space-y-12">
            <?php wp_nonce_field('logsfori_save_security', 'logsfori_nonce'); ?>
            <div id="repeater-container" class="space-y-2">
                <?php if (!empty($logsfori_security_hooks)): ?>
                    <?php foreach ($logsfori_security_hooks as $index => $hook): ?>
                        <div class="grid grid-cols-3  space-x-4 items-center bg-gray-100 p-2 rounded-md element-repeater">
                            <div class="!flex !items-center !rounded-md !bg-white !outline !outline-1  !-outline-offset-1 !outline-gray-300 !focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                <input style="height: 37px" value="<?php echo esc_attr($hook['hook_name']); ?>" type="text" placeholder="Hook Name"
                                       name="hooks[<?php echo $index; ?>][hook_name]" id="token"
                                       class="block min-w-0 !border-0 grow !py-1.5 !pl-2 !pr-3 !text-sm !text-gray-900 !placeholder:text-gray-400 focus:!border-none !focus:shadow-none !focus:outline !focus:outline-0 sm:text-sm/6">
                            </div>
                            <div>
                                <div class="grid grid-cols-1">
                                    <select style="height: 37px" name="hooks[<?php echo $index; ?>][severity]"
                                            class="col-start-1 row-start-1 !w-full appearance-none rounded-md bg-white !py-1.5 !pl-3 !pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                        <?php foreach (logsfori_get_severity_levels() as $key => $label): ?>
                                            <option value="<?php echo esc_attr($label); ?>" <?php selected($hook['severity'], $label); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <button type="button"
                                        class=" remove-hook cursor-pointer rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Delete this hook
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-hook"
                    class="cursor-pointer rounded-md bg-teal-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add a new hook
            </button>
        </div>
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="submit" name="security_settings"
                    class="cursor-pointer rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Save
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const repeaterContainer = document.getElementById("repeater-container");
        const addHookButton = document.getElementById("add-hook");

        addHookButton.addEventListener("click", function () {
            const index = repeaterContainer.children.length;

            // Création du nouvel élément
            const newRow = document.createElement("div");
            newRow.classList.add("grid", "grid-cols-3", "space-x-4", "items-center", "bg-gray-100", "p-4", "rounded-md", "element-repeater");

            newRow.innerHTML = `
                <div class="!flex !items-center !rounded-md !bg-white !outline !outline-1  !-outline-offset-1 !outline-gray-300 !focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input style="height: 37px" type="text" placeholder="Hook Name" name="hooks[${index}][hook_name]" id="hook_name_${index}"
                        class="block min-w-0 !border-0 grow !py-1.5 !pl-2 !pr-3 !text-sm !text-gray-900 !placeholder:text-gray-400 focus:!border-none !focus:shadow-none !focus:outline !focus:outline-0 sm:text-sm/6">
                </div>
                <div>
                    <div class="grid grid-cols-1">
                        <select style="height: 37px" name="hooks[${index}][severity]"
                            class="col-start-1 row-start-1 !w-full appearance-none rounded-md bg-white !py-1.5 !pl-3 !pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            <option value="debug">Debug</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div>
              <button type="button"
                                        class=" remove-hook cursor-pointer rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Delete this hook
                                </button>
                </div>
            `;

            // Ajoute le nouvel élément au repeater
            repeaterContainer.appendChild(newRow);
        });

        // Gestion des suppressions de ligne
        repeaterContainer.addEventListener("click", function (event) {
            if (event.target.classList.contains("remove-hook")) {
                event.target.closest(".element-repeater").remove();
            }
        });
    });
</script>

