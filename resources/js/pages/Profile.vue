<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Calendar, Mail, User } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout>

        <Head title="Profile" />

        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Profile</h1>
                <p class="text-muted-foreground">
                    View your account information and details
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Personal Information</CardTitle>
                        <CardDescription>
                            Your account details and information
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex items-center gap-4">
                            <UserInfo :user="user" :show-email="true" />
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <User class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Name</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ user.name }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <Mail class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Email</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ user.email }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <Calendar class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">
                                        Member since
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{
                                            new Date(
                                                user.created_at
                                            ).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                            })
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
