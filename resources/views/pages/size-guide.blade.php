@extends('layouts.app')

@section('title', 'Size Guide - VOID Supply')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Size Guide</h1>
            <div class="w-20 h-0.5 bg-void-accent mx-auto mt-4"></div>
        </div>
        
        <div class="bg-void-card border border-void-border rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-void-dark border-b border-void-border">
                        <tr>
                            <th class="text-left py-4 px-6 text-void-white font-bold">Size</th>
                            <th class="text-left py-4 px-6 text-void-white font-bold">Chest (cm)</th>
                            <th class="text-left py-4 px-6 text-void-white font-bold">Length (cm)</th>
                            <th class="text-left py-4 px-6 text-void-white font-bold">Shoulder (cm)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-void-border">
                        <tr><td class="py-4 px-6 text-void-light font-semibold">S</td><td class="py-4 px-6 text-void-gray">96-100</td><td class="py-4 px-6 text-void-gray">68-70</td><td class="py-4 px-6 text-void-gray">44-46</td></tr>
                        <tr><td class="py-4 px-6 text-void-light font-semibold">M</td><td class="py-4 px-6 text-void-gray">100-104</td><td class="py-4 px-6 text-void-gray">70-72</td><td class="py-4 px-6 text-void-gray">46-48</td></tr>
                        <tr><td class="py-4 px-6 text-void-light font-semibold">L</td><td class="py-4 px-6 text-void-gray">104-108</td><td class="py-4 px-6 text-void-gray">72-74</td><td class="py-4 px-6 text-void-gray">48-50</td></tr>
                        <tr><td class="py-4 px-6 text-void-light font-semibold">XL</td><td class="py-4 px-6 text-void-gray">108-112</td><td class="py-4 px-6 text-void-gray">74-76</td><td class="py-4 px-6 text-void-gray">50-52</td></tr>
                        <tr><td class="py-4 px-6 text-void-light font-semibold">XXL</td><td class="py-4 px-6 text-void-gray">112-116</td><td class="py-4 px-6 text-void-gray">76-78</td><td class="py-4 px-6 text-void-gray">52-54</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-xs text-void-muted">*Ukuran dapat berbeda +/- 1-2 cm tergantung bahan</p>
        </div>
    </div>
</div>
@endsection