@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto py-6 px-4">

    <div class="bg-white shadow-lg rounded-lg p-6">

        <h2 class="text-2xl font-bold text-blue-700 mb-4">
            Select Section
        </h2>

        <table class="w-full border border-gray-400">

            <thead class="bg-blue-200">

                <tr>
                    <th class="border p-2 w-32">
                        Action
                    </th>

                    <th class="border p-2">
                        Section Name
                    </th>
                </tr>

            </thead>

            <tbody>

            @foreach($records as $row)

                <tr class="hover:bg-yellow-50">

                    <td class="border p-2 text-center">

                        <a href="{{ route(
                            'selection.select',
                            [
                                $row->yearid,
                                $row->sectionid
                            ]
                        ) }}"
                        class="erp-btn erp-btn-add">

                            Select

                        </a>

                    </td>

                    <td class="border p-2">

                        {{ $sections[$row->sectionid] ?? $row->sectionid }}
                        {{ $row->yearid }}-{{ $row->yearid + 1 }}

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection