@props(['headers'])

<div class="block md:hidden">
    @foreach($items as $item)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-4">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $item[$headers[0]['key']] }}
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    @foreach($headers as $index => $header)
                        @if($index > 0)
                            <div class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    {{ $header['label'] }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $item[$header['key']] }}
                                </dd>
                            </div>
                        @endif
                    @endforeach
                    @if(isset($actions))
                        <div class="bg-gray-50 px-4 py-5 sm:px-6">
                            {{ $actions($item) }}
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    @endforeach
</div>

<div class="hidden md:block">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                @foreach($headers as $header)
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $header['label'] }}
                    </th>
                @endforeach
                @if(isset($actions))
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($items as $item)
                <tr>
                    @foreach($headers as $header)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item[$header['key']] }}
                        </td>
                    @endforeach
                    @if(isset($actions))
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            {{ $actions($item) }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
