<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type ValidationErrors = {
  amount?: string;
  recipient_user_id?: string;
};

type TransferUser = {
  id: number;
  name: string;
  email?: string;
};

const page = usePage();

const walletBalance = computed<number | null>(() => {
  const anyProps = page.props as any;
  const balance = anyProps?.wallet?.balance;
  return typeof balance === 'number' ? balance : null;
});

const users = computed<TransferUser[]>(() => {
  const anyProps = page.props as any;
  const list = anyProps?.users;
  return Array.isArray(list) ? list : [];
});

const formatAmount = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount / 100);
};

const depositAmount = ref<string>('');
const withdrawAmount = ref<string>('');
const transferAmount = ref<string>('');
const transferRecipientUserId = ref<string>('');

const depositErrors = ref<ValidationErrors>({});
const withdrawErrors = ref<ValidationErrors>({});
const transferErrors = ref<ValidationErrors>({});

const depositProcessing = ref(false);
const withdrawProcessing = ref(false);
const transferProcessing = ref(false);

const depositStatus = ref<string | null>(null);
const withdrawStatus = ref<string | null>(null);
const transferStatus = ref<string | null>(null);

const depositStatusIsError = ref(false);
const withdrawStatusIsError = ref(false);
const transferStatusIsError = ref(false);

const makeIdempotencyKey = () => crypto.randomUUID();

type ConfirmAction = 'deposit' | 'withdraw' | 'transfer';

const confirmOpen = ref(false);
const confirmAction = ref<ConfirmAction | null>(null);
const confirmTitle = ref('');
const confirmDescription = ref('');

const openConfirm = (action: ConfirmAction) => {
  confirmAction.value = action;

  if (action === 'deposit') {
    if (!depositAmount.value?.trim()) {
      void submitDeposit();
      return;
    }

    confirmTitle.value = 'Confirm deposit';
    confirmDescription.value = `Deposit ${depositAmount.value} into your wallet?`;
  }

  if (action === 'withdraw') {
    if (!withdrawAmount.value?.trim()) {
      void submitWithdraw();
      return;
    }

    confirmTitle.value = 'Confirm withdrawal';
    confirmDescription.value = `Withdraw ${withdrawAmount.value} from your wallet?`;
  }

  if (action === 'transfer') {
    if (!transferRecipientUserId.value?.trim() || !transferAmount.value?.trim()) {
      void submitTransfer();
      return;
    }

    const recipient = users.value.find((u) => String(u.id) === transferRecipientUserId.value);
    const recipientLabel = recipient ? `${recipient.name}${recipient.email ? ` (${recipient.email})` : ''}` : 'the selected user';

    confirmTitle.value = 'Confirm transfer';
    confirmDescription.value = `Transfer ${transferAmount.value} to ${recipientLabel}?`;
  }

  confirmOpen.value = true;
};

const handleConfirm = async () => {
  if (!confirmAction.value) return;

  const action = confirmAction.value;
  confirmOpen.value = false;
  confirmAction.value = null;

  if (action === 'deposit') await submitDeposit();
  if (action === 'withdraw') await submitWithdraw();
  if (action === 'transfer') await submitTransfer();
};

const baseHeaders = () => {
  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  } as Record<string, string>;
};

const parseErrorResponse = async (response: Response) => {
  try {
    return await response.json();
  } catch {
    return null;
  }
};

const refreshWalletProps = async (): Promise<void> => {
  return new Promise((resolve) => {
    router.reload({
      preserveScroll: true,
      preserveState: true,
      onFinish: () => resolve(),
    } as any);
  });
};

const submitDeposit = async () => {
  depositStatus.value = null;
  depositStatusIsError.value = false;
  depositErrors.value = {};
  depositProcessing.value = true;

  try {
    const response = await fetch('/api/wallet/deposit', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        ...baseHeaders(),
        'Idempotency-Key': makeIdempotencyKey(),
      },
      body: JSON.stringify({
        amount: depositAmount.value,
      }),
    });

    if (!response.ok) {
      const data = await parseErrorResponse(response);
      if (response.status === 422 && data?.errors) {
        depositErrors.value = {
          amount: data.errors.amount?.[0],
        };

        depositStatusIsError.value = true;
        return;
      }

      depositStatus.value = data?.message || `Deposit failed (${response.status})`;
      depositStatusIsError.value = true;
      return;
    }

    depositStatus.value = 'Deposit completed successfully.';
    depositStatusIsError.value = false;
    depositAmount.value = '';

    await refreshWalletProps();
  } finally {
    depositProcessing.value = false;
  }
};

const submitWithdraw = async () => {
  withdrawStatus.value = null;
  withdrawStatusIsError.value = false;
  withdrawErrors.value = {};
  withdrawProcessing.value = true;

  try {
    const response = await fetch('/api/wallet/withdraw', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        ...baseHeaders(),
        'Idempotency-Key': makeIdempotencyKey(),
      },
      body: JSON.stringify({
        amount: withdrawAmount.value,
      }),
    });

    if (!response.ok) {
      const data = await parseErrorResponse(response);
      if (response.status === 422 && data?.errors) {
        withdrawErrors.value = {
          amount: data.errors.amount?.[0],
        };

        withdrawStatusIsError.value = true;
        return;
      }

      withdrawStatus.value = data?.message || `Withdrawal failed (${response.status})`;
      withdrawStatusIsError.value = true;
      return;
    }

    withdrawStatus.value = 'Withdrawal completed successfully.';
    withdrawStatusIsError.value = false;
    withdrawAmount.value = '';

    await refreshWalletProps();
  } finally {
    withdrawProcessing.value = false;
  }
};

const submitTransfer = async () => {
  transferStatus.value = null;
  transferStatusIsError.value = false;
  transferErrors.value = {};
  transferProcessing.value = true;

  try {
    const response = await fetch('/api/wallet/transfer', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        ...baseHeaders(),
        'Idempotency-Key': makeIdempotencyKey(),
      },
      body: JSON.stringify({
        recipient_user_id: transferRecipientUserId.value,
        amount: transferAmount.value,
      }),
    });

    if (!response.ok) {
      const data = await parseErrorResponse(response);
      if (response.status === 422 && data?.errors) {
        transferErrors.value = {
          amount: data.errors.amount?.[0],
          recipient_user_id: data.errors.recipient_user_id?.[0],
        };

        transferStatusIsError.value = true;
        return;
      }

      transferStatus.value = data?.message || `Transfer failed (${response.status})`;
      transferStatusIsError.value = true;
      return;
    }

    transferStatus.value = 'Transfer completed successfully.';
    transferStatusIsError.value = false;
    transferAmount.value = '';
    transferRecipientUserId.value = '';

    await refreshWalletProps();
  } finally {
    transferProcessing.value = false;
  }
};
</script>

<template>
  <AppLayout>

    <Head title="Wallet" />

    <div class="space-y-6 p-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Wallet</h1>
        <p class="text-muted-foreground mt-1">Manage deposits and withdrawals</p>
        <p v-if="walletBalance !== null" class="text-muted-foreground mt-1">
          Current balance: <span class="font-medium text-foreground">{{ formatAmount(walletBalance) }}</span>
        </p>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Deposit</CardTitle>
            <CardDescription>Add funds to your wallet</CardDescription>
          </CardHeader>
          <CardContent>
            <form class="space-y-4" @submit.prevent="openConfirm('deposit')">
              <div class="grid gap-2">
                <Label for="deposit-amount">Amount</Label>
                <Input id="deposit-amount" v-model="depositAmount" name="amount" inputmode="decimal" placeholder="0.00"
                  :disabled="depositProcessing" />
                <InputError :message="depositErrors.amount" />
              </div>

              <p v-if="depositStatus" class="text-sm"
                :class="depositStatusIsError ? 'text-red-600 dark:text-red-500' : 'text-green-600'">
                {{ depositStatus }}
              </p>

              <Button type="submit" class="w-full" :disabled="depositProcessing">
                <Spinner v-if="depositProcessing" />
                Deposit
              </Button>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Withdraw</CardTitle>
            <CardDescription>Remove funds from your wallet</CardDescription>
          </CardHeader>
          <CardContent>
            <form class="space-y-4" @submit.prevent="openConfirm('withdraw')">
              <div class="grid gap-2">
                <Label for="withdraw-amount">Amount</Label>
                <Input id="withdraw-amount" v-model="withdrawAmount" name="amount" inputmode="decimal"
                  placeholder="0.00" :disabled="withdrawProcessing" />
                <InputError :message="withdrawErrors.amount" />
              </div>

              <p v-if="withdrawStatus" class="text-sm"
                :class="withdrawStatusIsError ? 'text-red-600 dark:text-red-500' : 'text-green-600'">
                {{ withdrawStatus }}
              </p>

              <Button type="submit" class="w-full" variant="destructive" :disabled="withdrawProcessing">
                <Spinner v-if="withdrawProcessing" />
                Withdraw
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>

      <Card class="md:col-span-2">
        <CardHeader>
          <CardTitle>Transfer</CardTitle>
          <CardDescription>Send money to another user</CardDescription>
        </CardHeader>
        <CardContent>
          <form class="space-y-4" @submit.prevent="openConfirm('transfer')">
            <div class="grid gap-2">
              <Label for="transfer-recipient">Recipient</Label>
              <div class="relative">
                <select id="transfer-recipient" v-model="transferRecipientUserId" :disabled="transferProcessing"
                  class="border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 h-9 w-full min-w-0 appearance-none rounded-md border bg-transparent px-3 py-1 pr-9 text-base text-foreground shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]">
                  <option value="" disabled>Select a user</option>
                  <option v-for="user in users" :key="user.id" :value="String(user.id)">
                    {{ user.name }}<template v-if="user.email"> ({{ user.email }})</template>
                  </option>
                </select>
                <ChevronDown
                  class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
              </div>
              <InputError :message="transferErrors.recipient_user_id" />
            </div>

            <div class="grid gap-2">
              <Label for="transfer-amount">Amount</Label>
              <Input id="transfer-amount" v-model="transferAmount" name="amount" inputmode="decimal" placeholder="0.00"
                :disabled="transferProcessing" />
              <InputError :message="transferErrors.amount" />
            </div>

            <p v-if="transferStatus" class="text-sm"
              :class="transferStatusIsError ? 'text-red-600 dark:text-red-500' : 'text-green-600'">
              {{ transferStatus }}
            </p>

            <Button type="submit" class="w-full" variant="secondary" :disabled="transferProcessing || !users.length">
              <Spinner v-if="transferProcessing" />
              Transfer
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>

    <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
      <DialogContent>
        <DialogHeader class="space-y-3">
          <DialogTitle>{{ confirmTitle }}</DialogTitle>
          <DialogDescription>
            {{ confirmDescription }}
          </DialogDescription>
        </DialogHeader>

        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button type="button" variant="destructive"
            :disabled="depositProcessing || withdrawProcessing || transferProcessing" @click="handleConfirm">
            Confirm
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
