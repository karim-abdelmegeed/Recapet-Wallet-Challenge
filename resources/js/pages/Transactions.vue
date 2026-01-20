<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface Transaction {
    id: number;
    type: 'deposit' | 'withdrawal' | 'transfer' | 'fee';
    status: 'pending' | 'completed' | 'failed' | 'reversed';
    amount: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTransactions {
    data: Transaction[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const page = usePage();
const transactions = computed<PaginatedTransactions>(() => page.props.transactions as PaginatedTransactions);

const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount / 100);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'failed':
            return 'destructive';
        case 'reversed':
            return 'outline';
        default:
            return 'default';
    }
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'deposit':
            return 'default';
        case 'withdrawal':
            return 'secondary';
        case 'transfer':
            return 'outline';
        case 'fee':
            return 'destructive';
        default:
            return 'default';
    }
};
</script>

<template>
    <AppLayout>

        <Head title="Transactions" />

        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Transactions</h1>
                <p class="text-muted-foreground mt-1">
                    View your transaction history and details
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Transaction History</CardTitle>
                    <CardDescription v-if="transactions.total">
                        Showing {{ (transactions.current_page - 1) * transactions.per_page + 1 }} to
                        {{ Math.min(transactions.current_page * transactions.per_page, transactions.total) }} of
                        {{ transactions.total }} transactions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Date
                                        </th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Type
                                        </th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Status
                                        </th>
                                        <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                            Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="transactions.data && transactions.data.length === 0"
                                        class="border-b transition-colors hover:bg-muted/50">
                                        <td colspan="4" class="h-24 px-4 text-center text-muted-foreground">
                                            No transactions found
                                        </td>
                                    </tr>
                                    <tr v-for="transaction in transactions.data" :key="transaction.id"
                                        class="border-b transition-colors hover:bg-muted/50">
                                        <td class="px-4 py-4 align-middle">
                                            <div class="text-sm">{{ formatDate(transaction.created_at) }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-middle">
                                            <Badge :variant="getTypeVariant(transaction.type)">
                                                {{ transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)
                                                }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-4 align-middle">
                                            <Badge :variant="getStatusVariant(transaction.status)">
                                                {{ transaction.status.charAt(0).toUpperCase() +
                                                transaction.status.slice(1) }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-4 text-right align-middle">
                                            <div class="text-sm font-medium" :class="{
                                                'text-green-600 dark:text-green-400': transaction.type === 'deposit',
                                                'text-red-600 dark:text-red-400': transaction.type === 'withdrawal' || transaction.type === 'fee',
                                                'text-blue-600 dark:text-blue-400': transaction.type === 'transfer',
                                            }">
                                                {{ transaction.type === 'deposit' ? '+' : '-' }}{{
                                                formatAmount(transaction.amount) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="transactions.links && transactions.links.length > 3"
                            class="flex items-center justify-between border-t px-4 py-3">
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" :disabled="transactions.current_page === 1"
                                    :as-child="transactions.current_page !== 1">
                                    <Link v-if="transactions.current_page !== 1"
                                        :href="transactions.links[0].url || '#'">
                                        <ArrowLeft class="mr-2 h-4 w-4" />
                                        Previous
                                    </Link>
                                    <span v-else>
                                        <ArrowLeft class="mr-2 h-4 w-4" />
                                        Previous
                                    </span>
                                </Button>
                            </div>

                            <div class="flex items-center gap-1">
                                <Button v-for="(link, index) in transactions.links" :key="index"
                                    :variant="link.active ? 'default' : 'outline'" size="sm"
                                    :disabled="!link.url || link.active" :as-child="!!link.url && !link.active"
                                    class="min-w-[2.5rem]">
                                    <Link v-if="link.url && !link.active" :href="link.url">
                                        <span v-html="link.label"></span>
                                    </Link>
                                    <span v-else>
                                        <span v-html="link.label"></span>
                                    </span>
                                </Button>
                            </div>

                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm"
                                    :disabled="transactions.current_page === transactions.last_page"
                                    :as-child="transactions.current_page !== transactions.last_page">
                                    <Link v-if="transactions.current_page !== transactions.last_page"
                                        :href="transactions.links[transactions.links.length - 1].url || '#'">
                                        Next
                                        <ArrowRight class="ml-2 h-4 w-4" />
                                    </Link>
                                    <span v-else>
                                        Next
                                        <ArrowRight class="ml-2 h-4 w-4" />
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
