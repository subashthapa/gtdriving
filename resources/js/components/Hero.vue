<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const slides = [
    {
        image: '/img/6662186.jpg',
        eyebrow: 'Learn with confidence',
        title: 'GT Driving Solutions',
        copy: 'Your path to safe and confident driving.',
        position: 'center',
    },
    {
        image: '/img/learner-driver-ron-lach.jpg',
        eyebrow: 'Patient, practical instruction',
        title: 'Build skills for every road',
        copy: 'Personalised lessons that turn new drivers into calm, capable drivers.',
        position: 'center 44%',
    },
];

const activeSlide = ref(0);
let rotationTimer;

const selectSlide = (index) => {
    activeSlide.value = index;
};

const showNextSlide = () => {
    activeSlide.value = (activeSlide.value + 1) % slides.length;
};

const showPreviousSlide = () => {
    activeSlide.value = (activeSlide.value - 1 + slides.length) % slides.length;
};

const stopRotation = () => {
    if (rotationTimer) {
        window.clearInterval(rotationTimer);
        rotationTimer = undefined;
    }
};

const startRotation = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    stopRotation();
    rotationTimer = window.setInterval(showNextSlide, 6500);
};

onMounted(startRotation);
onBeforeUnmount(stopRotation);
</script>

<template>
    <header
        class="relative isolate h-screen min-h-[680px] overflow-hidden bg-slate-950 text-white"
        aria-roledescription="carousel"
        aria-label="GT Driving highlights"
        @mouseenter="stopRotation"
        @mouseleave="startRotation"
        @focusin="stopRotation"
        @focusout="startRotation"
    >
        <div
            v-for="(slide, index) in slides"
            :key="slide.image"
            class="absolute inset-0 bg-cover transition-opacity duration-1000 motion-reduce:transition-none"
            :class="index === activeSlide ? 'opacity-100' : 'opacity-0'"
            :style="{ backgroundImage: `url('${slide.image}')`, backgroundPosition: slide.position }"
            role="group"
            aria-roledescription="slide"
            :aria-label="`${index + 1} of ${slides.length}`"
            :aria-hidden="index !== activeSlide"
        />

        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/55 to-slate-950/20" />
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent" />

        <div class="relative mx-auto flex h-full max-w-7xl items-center px-6 pb-24 pt-32 sm:px-8 lg:px-12">
            <div class="max-w-3xl text-left">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.28em] text-amber-300 sm:text-base">
                    {{ slides[activeSlide].eyebrow }}
                </p>
                <h1 class="text-4xl font-bold leading-[1.05] sm:text-6xl lg:text-7xl">
                    {{ slides[activeSlide].title }}
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-100 sm:text-2xl">
                    {{ slides[activeSlide].copy }}
                </p>
                <a href="#book" class="mt-10 inline-flex items-center rounded-md bg-amber-400 px-8 py-4 text-lg font-bold text-slate-950 shadow-lg transition hover:bg-amber-300 focus:outline-none focus:ring-4 focus:ring-amber-200/60">
                    Book a lesson
                </a>
            </div>
        </div>

        <div class="absolute inset-x-0 bottom-8 z-10 mx-auto flex max-w-7xl items-center justify-between px-6 sm:px-8 lg:px-12">
            <div class="flex gap-3" aria-label="Choose a slide">
                <button
                    v-for="(_, index) in slides"
                    :key="index"
                    type="button"
                    class="h-2.5 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-white"
                    :class="index === activeSlide ? 'w-10 bg-amber-400' : 'w-2.5 bg-white/60 hover:bg-white'"
                    :aria-label="`Show slide ${index + 1}`"
                    :aria-current="index === activeSlide ? 'true' : undefined"
                    @click="selectSlide(index)"
                />
            </div>

            <div class="flex gap-2">
                <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-white/60 bg-slate-950/30 text-2xl backdrop-blur-sm transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Previous slide" @click="showPreviousSlide">
                    <span aria-hidden="true">&#8249;</span>
                </button>
                <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-white/60 bg-slate-950/30 text-2xl backdrop-blur-sm transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Next slide" @click="showNextSlide">
                    <span aria-hidden="true">&#8250;</span>
                </button>
            </div>
        </div>
    </header>
</template>
