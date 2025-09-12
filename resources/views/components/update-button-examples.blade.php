{{-- Update Button Skeleton - Usage Examples --}}
{{-- This file shows how to use the update-button-skeleton component --}}

<div class="container mx-auto p-6 space-y-8">
    <h1 class="text-2xl font-bold mb-6">Update Button Skeleton - Usage Examples</h1>
    
    {{-- Basic Update Button --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Basic Update Button</h2>
        <div class="flex space-x-4 mb-3">
            <x-update-button-skeleton label="Update Record" />
            <x-update-button-skeleton label="Save Changes" icon="fas fa-save" />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton label="Update Record" /&gt;
&lt;x-update-button-skeleton label="Save Changes" icon="fas fa-save" /&gt;</code></pre>
    </div>

    {{-- Different Sizes --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Different Sizes</h2>
        <div class="flex space-x-4 items-center mb-3">
            <x-update-button-skeleton label="Small" size="small" />
            <x-update-button-skeleton label="Medium" size="medium" />
            <x-update-button-skeleton label="Large" size="large" />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton label="Small" size="small" /&gt;
&lt;x-update-button-skeleton label="Medium" size="medium" /&gt;
&lt;x-update-button-skeleton label="Large" size="large" /&gt;</code></pre>
    </div>

    {{-- Different Variants --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Different Variants</h2>
        <div class="flex space-x-2 mb-3 flex-wrap">
            <x-update-button-skeleton label="Primary" variant="primary" />
            <x-update-button-skeleton label="Secondary" variant="secondary" />
            <x-update-button-skeleton label="Success" variant="success" icon="fas fa-check" />
            <x-update-button-skeleton label="Warning" variant="warning" icon="fas fa-exclamation-triangle" />
            <x-update-button-skeleton label="Danger" variant="danger" icon="fas fa-trash" />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton label="Primary" variant="primary" /&gt;
&lt;x-update-button-skeleton label="Success" variant="success" icon="fas fa-check" /&gt;</code></pre>
    </div>

    {{-- Loading and Disabled States --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Loading and Disabled States</h2>
        <div class="flex space-x-4 mb-3">
            <x-update-button-skeleton label="Updating..." :loading="true" />
            <x-update-button-skeleton label="Disabled" :disabled="true" />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton label="Updating..." :loading="true" /&gt;
&lt;x-update-button-skeleton label="Disabled" :disabled="true" /&gt;</code></pre>
    </div>

    {{-- Form Submit Buttons --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Form Submit Buttons</h2>
        <div class="space-y-3 mb-3">
            <x-update-button-skeleton 
                label="Update Patient" 
                type="submit" 
                action="/patients/1" 
                method="PATCH" 
                icon="fas fa-user-edit" 
                variant="success" 
            />
            <x-update-button-skeleton 
                label="Delete Record" 
                type="submit" 
                action="/records/1" 
                method="DELETE" 
                icon="fas fa-trash" 
                variant="danger" 
                :confirm="true" 
                confirm-message="Are you sure you want to delete this record? This action cannot be undone." 
            />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton 
    label="Update Patient" 
    type="submit" 
    action="/patients/1" 
    method="PATCH" 
    icon="fas fa-user-edit" 
    variant="success" 
/&gt;</code></pre>
    </div>

    {{-- Link Buttons --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Link Buttons</h2>
        <div class="flex space-x-4 mb-3">
            <x-update-button-skeleton 
                label="Edit Profile" 
                type="link" 
                action="/profile/edit" 
                icon="fas fa-edit" 
                variant="secondary" 
            />
            <x-update-button-skeleton 
                label="View Report" 
                type="link" 
                action="/reports/1" 
                icon="fas fa-file-alt" 
                variant="primary" 
            />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton 
    label="Edit Profile" 
    type="link" 
    action="/profile/edit" 
    icon="fas fa-edit" 
    variant="secondary" 
/&gt;</code></pre>
    </div>

    {{-- Custom JavaScript Actions --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Custom JavaScript Actions</h2>
        <div class="flex space-x-4 mb-3">
            <x-update-button-skeleton 
                label="Refresh Data" 
                icon="fas fa-sync-alt" 
                onclick="refreshData()" 
            />
            <x-update-button-skeleton 
                label="Export CSV" 
                icon="fas fa-download" 
                variant="success" 
                onclick="exportData('csv')" 
            />
        </div>
        <pre class="bg-gray-100 p-3 text-sm rounded"><code>&lt;x-update-button-skeleton 
    label="Refresh Data" 
    icon="fas fa-sync-alt" 
    onclick="refreshData()" 
/&gt;</code></pre>
    </div>

    {{-- Component Props Documentation --}}
    <div class="example-section">
        <h2 class="text-lg font-semibold mb-3">Available Props</h2>
        <div class="bg-gray-50 p-4 rounded">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Prop</th>
                        <th class="text-left p-2">Type</th>
                        <th class="text-left p-2">Default</th>
                        <th class="text-left p-2">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-2 font-mono">label</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'Update'</td>
                        <td class="p-2">Button text</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">icon</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'fas fa-sync-alt'</td>
                        <td class="p-2">FontAwesome icon class</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">size</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'medium'</td>
                        <td class="p-2">small, medium, large</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">variant</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'primary'</td>
                        <td class="p-2">primary, secondary, success, warning, danger</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">loading</td>
                        <td class="p-2">boolean</td>
                        <td class="p-2">false</td>
                        <td class="p-2">Shows loading spinner</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">disabled</td>
                        <td class="p-2">boolean</td>
                        <td class="p-2">false</td>
                        <td class="p-2">Disables the button</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">type</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'button'</td>
                        <td class="p-2">button, submit, link</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">action</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'#'</td>
                        <td class="p-2">URL for form action or link href</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">method</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'POST'</td>
                        <td class="p-2">HTTP method for forms</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2 font-mono">confirm</td>
                        <td class="p-2">boolean</td>
                        <td class="p-2">false</td>
                        <td class="p-2">Show confirmation dialog</td>
                    </tr>
                    <tr>
                        <td class="p-2 font-mono">confirmMessage</td>
                        <td class="p-2">string</td>
                        <td class="p-2">'Are you sure...'</td>
                        <td class="p-2">Confirmation dialog text</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Example JavaScript functions for the custom actions
    function refreshData() {
        alert('Refreshing data...');
        // Add your refresh logic here
    }
    
    function exportData(format) {
        alert('Exporting data as ' + format.toUpperCase());
        // Add your export logic here
    }
</script>