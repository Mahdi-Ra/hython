<h1>نامه‌ها</h1>

<ul class="nav nav-tabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inbox">دریافتی</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sent">ارسالی</button>
    </li>
</ul>

<div class="tab-content mt-3">

    {{-- Inbox --}}
    <div class="tab-pane fade show active" id="inbox">
        @foreach($receivedLetters as $letter)
            <div class="card mb-2">
                <div class="card-body">
                    <b>{{ $letter->subject }}</b>
                    <p class="mb-1">{{ Str::limit($letter->body, 100) }}</p>
                    <small>{{ $letter->created_at }}</small>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Sent --}}
    <div class="tab-pane fade" id="sent">
        @foreach($sentLetters as $letter)
            <div class="card mb-2">
                <div class="card-body">
                    <b>{{ $letter->subject }}</b>
                    <p class="mb-1">{{ Str::limit($letter->body, 100) }}</p>
                    <small>{{ $letter->created_at }}</small>
                </div>
            </div>
        @endforeach
    </div>

</div>
