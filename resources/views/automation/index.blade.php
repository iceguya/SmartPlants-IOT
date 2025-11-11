<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Automation Rules - {{ $device->name }}
            </h2>
            <a href="{{ route('device.show', $device) }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Device
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Add New Rule Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Add New Automation Rule</h3>
                    
                    <form method="POST" action="{{ route('device.automation.store', $device) }}" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Condition Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                                <select name="condition_type" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="soil_low">Soil Moisture Low (less than)</option>
                                    <option value="soil_high">Soil Moisture High (greater than)</option>
                                    <option value="temp_low">Temperature Low (less than)</option>
                                    <option value="temp_high">Temperature High (greater than)</option>
                                    <option value="hum_low">Humidity Low (less than)</option>
                                    <option value="hum_high">Humidity High (greater than)</option>
                                </select>
                            </div>

                            <!-- Threshold Value -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Threshold Value (%)</label>
                                <input type="number" name="threshold_value" min="0" max="100" step="0.1" value="30" required 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <!-- Action Duration -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Watering Duration (seconds)</label>
                                <input type="number" name="action_duration" min="1" max="60" value="5" required 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <!-- Cooldown -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cooldown (minutes)</label>
                                <input type="number" name="cooldown_minutes" min="5" max="1440" value="60" required 
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Minimum time between triggers</p>
                            </div>
                        </div>

                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Add Rule
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Rules -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Active Rules</h3>

                    @if($rules->isEmpty())
                        <p class="text-gray-500">No automation rules configured yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($rules as $rule)
                                <div class="flex items-center justify-between p-4 border rounded-lg {{ $rule->enabled ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $rule->enabled ? 'bg-green-600 text-white' : 'bg-gray-400 text-white' }}">
                                                {{ $rule->enabled ? 'ENABLED' : 'DISABLED' }}
                                            </span>
                                            <span class="font-medium text-gray-900">
                                                {{ ucwords(str_replace('_', ' ', $rule->condition_type)) }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p>Trigger when value {{ $rule->condition_type === 'soil_low' || $rule->condition_type === 'temp_low' || $rule->condition_type === 'hum_low' ? '<' : '>' }} {{ $rule->threshold_value }}%</p>
                                            <p>Action: Water ON for {{ $rule->action_duration }} seconds</p>
                                            <p>Cooldown: {{ $rule->cooldown_minutes }} minutes</p>
                                            @if($rule->last_triggered_at)
                                                <p class="text-xs text-gray-500 mt-1">Last triggered: {{ $rule->last_triggered_at->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <!-- Toggle -->
                                        <form method="POST" action="{{ route('device.automation.toggle', [$device, $rule]) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-sm rounded {{ $rule->enabled ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white transition-colors">
                                                {{ $rule->enabled ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('device.automation.destroy', [$device, $rule]) }}" onsubmit="return confirm('Delete this rule?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 text-sm bg-red-500 hover:bg-red-600 text-white rounded transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
