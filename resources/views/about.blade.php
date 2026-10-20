@extends('layouts.app')

@section('title', 'About - ' . config('app.name'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">About</h1>

    <div class="max-w-3xl text-gray-700 leading-loose">
        <p class="text-2xl font-bold text-gray-900 mb-2">
            はじめまして、<strong class="text-indigo-600">中山健太郎</strong>です。
        </p>
        <p>
            埼玉県出身、2026年春から鳥取県で暮らしています。
            現在はWebエンジニアとして働くことを目指し、Webアプリケーション開発を学んでいます。
        </p>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3 mb-4">
                これまでの経歴
            </h2>
            <div class="space-y-3">
                <p>
                    慶應義塾大学理工学部で、リチウム二次電池のサイクル特性と構成材料について研究しました。
                </p>
                <p>
                    卒業後は家庭の事情で大学院進学を見送り、接客のアルバイトや家庭教師(中高生に英語・数学・物理・化学を指導)を続けてきました。
                    相手の状況を聞き取り、わかりやすく伝える経験を積めたと思っています。
                </p>
            </div>
        </section>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3 mb-4">
                エンジニアを目指したきっかけ
            </h2>
            <div class="space-y-3">
                <p>
                    家族のサポートをする中でオンライン診療などのシステムに助けられ、ITが困っている人の大きな支えになることを実感しました。
                    特に移動手段が限られる地方では、オンライン化をはじめとするIT技術が暮らしを支える力は大きいと考えています。
                </p>
                <p>
                    2025年夏から独学を始め、2026年7月からは鳥取県DX人材育成プログラムでPHP・Laravelを使ったWebアプリ制作やチーム開発を学んでいます。
                    自分でコードを書いて基礎を固めるとともに、要件定義から設計を自分で行い、AIを活用して機能を実装していくAI駆動開発にも取り組んでいます。
                </p>
            </div>
        </section>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3 mb-4">
                スキル
            </h2>
            <dl class="grid gap-4">
                <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 items-start">
                    <dt class="font-semibold text-gray-900 sm:pt-1">主に使用</dt>
                    <dd>
                        <ul class="flex flex-wrap gap-2">
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">PHP / Laravel</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">HTML / CSS</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">JavaScript</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">SQL</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">Claude Code</li>
                        </ul>
                    </dd>
                </div>
                <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 items-start">
                    <dt class="font-semibold text-gray-900 sm:pt-1">学習経験あり</dt>
                    <dd>
                        <ul class="flex flex-wrap gap-2">
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">Python / Flask</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">TypeScript</li>
                            <li class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">React</li>
                        </ul>
                    </dd>
                </div>
                <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 items-start">
                    <dt class="font-semibold text-gray-900 sm:pt-1">触れたことがある</dt>
                    <dd>
                        <ul class="flex flex-wrap gap-2">
                            <li class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm">Linux / Nginx</li>
                            <li class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm">Vercel</li>
                            <li class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm">Heroku</li>
                        </ul>
                        <p class="mt-2 text-sm text-gray-500">
                            MoneyLog公開時にVPS上でLinux・Nginxの環境を構築。TabiPath・MealMateはVercelで公開。
                        </p>
                    </dd>
                </div>
                <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 items-start">
                    <dt class="font-semibold text-gray-900">資格</dt>
                    <dd>基本情報技術者試験</dd>
                </div>
            </dl>
        </section>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3 mb-4">
                これから
            </h2>
            <p>
                鳥取に根ざして、地域の暮らしや仕事を支えるシステムづくりに携わりたいと考えています。
                まずは確かな基礎を身につけ、チームで信頼されるエンジニアを目指します。
            </p>
        </section>
    </div>
@endsection