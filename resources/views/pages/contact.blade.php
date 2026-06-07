@extends('layouts.app')

@section('title', 'Hubungi Kami - VOID Supply')

@section('content')
<div class="pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-black text-void-accent tracking-tight">Hubungi Kami</h1>
            <div class="w-20 h-0.5 bg-void-accent mx-auto mt-4"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-void-white">Email</h3>
                </div>
                <a href="mailto:hello@voidsupply.com" class="text-void-gray hover:text-void-accent transition-colors">hello@voidsupply.com</a>
            </div>
            
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-void-white">WhatsApp</h3>
                </div>
                <a href="https://wa.me/6281234567890" target="_blank" class="text-void-gray hover:text-void-accent transition-colors">+62 812 3456 7890</a>
            </div>
            
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-void-white">Instagram</h3>
                </div>
                <a href="https://instagram.com/voidsupply" target="_blank" class="text-void-gray hover:text-void-accent transition-colors">@voidsupply</a>
            </div>
            
            <div class="bg-void-card border border-void-border rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-void-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-void-white">Jam Operasional</h3>
                </div>
                <p class="text-void-gray">Senin - Jumat: 09.00 - 17.00 WIB</p>
            </div>
        </div>
    </div>
</div>
@endsection