<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps<{
    canRegister: boolean;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">

        <Head title="Welcome" />

        <div class="w-full max-w-2xl">
            <div class="flex flex-col items-center gap-8 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-md">
                        <AppLogoIcon class="size-16 fill-current text-[var(--foreground)] dark:text-white" />
                    </div>
                    <h1 class="text-4xl font-bold tracking-tight">
                        Welcome to Banking System
                    </h1>
                    <p class="max-w-md text-lg text-muted-foreground">
                        Secure, reliable, and modern banking solutions for all
                        your financial needs.
                    </p>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row">
                    <Button as-child size="lg" class="gap-2">
                        <Link :href="login()">
                            Log in
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button v-if="canRegister" as-child size="lg" variant="outline" class="gap-2">
                        <Link :href="register()">
                            Create account
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
