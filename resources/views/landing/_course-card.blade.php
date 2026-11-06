<article class="course-card">
    <a class="course-image" href="{{ route('courses.show', $course) }}" @if ($course->image_path) style="background-image: url('{{ Storage::disk('public')->url($course->image_path) }}')" @endif>
        @unless ($course->image_path)<span class="course-image-symbol" aria-hidden="true">✳</span>@endunless
        <span class="course-category">{{ $course->category->name }}</span>
    </a>
    <div class="course-card-body">
        <div class="course-meta"><span>Oleh {{ $course->instructor }}</span><span>{{ $course->enrollments_count ?? $course->enrollments()->count() }} peserta</span></div>
        <h3><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($course->description), 112) }}</p>
        <div class="course-card-bottom"><span class="price">{{ (float) $course->price === 0.0 ? 'Gratis' : 'Rp '.number_format((float) $course->price, 0, ',', '.') }}</span><a class="arrow-link" href="{{ route('courses.show', $course) }}" aria-label="Lihat {{ $course->title }}">→</a></div>
    </div>
</article>
