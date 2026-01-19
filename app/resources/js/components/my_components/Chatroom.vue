<script setup lang="ts">
import { ref, nextTick, onMounted, useTemplateRef, ShallowRef } from 'vue';
import { useEchoPublic } from "@laravel/echo-vue";
import {
    Send as SendIcon,
    Bot,
    Copy,
    RefreshCw,
    Volume2,
    Paperclip,
    Mic,
    User as UserIcon
} from 'lucide-vue-next';
import { User } from "@/types";
import { encryptMessage, decryptMessage } from '@/utils';

// props
const {user, userPrivateKey, otherUser} = defineProps<{
    user: User;
    userPrivateKey: CryptoKey,
    otherUser: User;
}>();
console.log('user:', user);
console.log('other user:', otherUser);

// emits
const emit = defineEmits<{
    newOtherUserPublicKey: [newPublicKey: JsonWebKey];
}>();

interface Message {
    id: number;
    message: string;
    sender: User;
}
const messages = ref<Message[]>([]);
const newMessage = ref<string>('');
const messageIdCounter = ref<number>(0);
const isSending = ref<boolean>(false);
const messagesContainerElement = <Readonly<ShallowRef<HTMLDivElement>>>useTemplateRef("messagesContainer");
const messageInputElement = <Readonly<ShallowRef<HTMLInputElement>>>useTemplateRef("messageInput");
// let eventSource = null;

// websocket events
interface MessageRelayed {
    from: string,
    to: string,
    message: string,
}
useEchoPublic(
    "chatroom",
    "RelayMessage",
    handleMessageRelayed
);

interface UserJoinedMessage {
    userName: string;
    publicKey: JsonWebKey;
}
useEchoPublic(
    "chatroom",
    "UserJoined",
    (message: UserJoinedMessage): void => {
        console.log("new user joined:", message);
        emit("newOtherUserPublicKey", message.publicKey);
    }
);

async function handleMessageRelayed(messageRelayed: MessageRelayed) {
    // ignore messages coming from the user
    if (messageRelayed.from === user.name) {
        return;
    }
    // decrypt the message received with the user's private key
    const decryptedMessage = await decryptMessage(messageRelayed.message, userPrivateKey);
    // add the message to the list of messages
    addMessage(otherUser, decryptedMessage);
}

function addMessage(sender: User, content: string) {
    messageIdCounter.value++;
    const userMessage = {
        id: messageIdCounter.value,
        message: content,
        sender,
    };
    messages.value.push(userMessage);
}

// const scrollToBottom = async () => {
async function scrollToBottom() {
    await nextTick();
    // if (messagesContainerElement.value) {
    messagesContainerElement.value.scrollTop = messagesContainerElement.value.scrollHeight;
    // }
}

// const sendMessage = async () => {
async function sendMessage() {
    if (newMessage.value.trim() && !isSending.value) {
        if (otherUser.publicKey === null) {
            console.error("can't send message, otherUser public key is null")
            return;
        }
        console.log(`sending message "${newMessage.value}"`);
        isSending.value = true;
        const response = await fetch("/api/send-message", {
            method: "post",
            headers: {
                "Content-Type": "application/json; charset=utf-8",
                "Accept": "application/json; charset=utf-8"
            },
            body: JSON.stringify({
                from: user.name,
                to: otherUser.name,
                // message: newMessage.value,
                message: await encryptMessage(newMessage.value, otherUser.publicKey),
            }),
        });
        isSending.value = false;
        if (response.ok) {
            console.log("API response:", await response.json());
            // messageIdCounter.value++;
            // const userMessage = {
            //     id: messageIdCounter.value,
            //     message: newMessage.value,
            //     sender: user,
            // };
            // messages.value.push(userMessage);
            addMessage(user, newMessage.value);
            newMessage.value = '';
            messageInputElement.value.focus();
            await scrollToBottom();
        }
        else {
            console.error("The request failed for an unknown reason.", response);
        }
    }
}

// const copyMessage = (content) => {
// function copyMessage(content: string) {
//     navigator.clipboard.writeText(content);
// };

// Cleanup EventSource on component unmount
// onUnmounted(() => {
//     if (eventSource) {
//         eventSource.close();
//     }
// });

onMounted(() => {
    messageInputElement.value.focus();
});
</script>

<template>
    <!-- Chat messages -->
    <div class="flex flex-col gap-6 flex-1 overflow-y-auto p-4" ref="messagesContainer">
        <TransitionGroup name="fade">
            <div
                v-for="message in messages"
                :key="message.id"
                :class="[
                    'flex gap-3',
                    message.sender === user ? 'flex-row-reverse' : 'flex-row',
                ]"
            >
                <!-- Avatar -->
                <!-- <div v-if="message.sender === 'ai'" class="px-2.5 bg-gray-900 rounded-full flex items-center">
                    <Bot class="w-6 h-6 text-blue-400" />
                </div>
                <div v-else class="px-2.5 bg-gray-900 rounded-full flex items-center">
                    <UserIcon class="w-6 h-6 text-blue-400" />
                </div> -->
                <div class="px-3.5 bg-gray-900 rounded-full flex items-center text-xl text-blue-300">
                    <!-- <Bot class="w-6 h-6 text-blue-400" /> -->
                    {{ message.sender.name[0].toUpperCase() }}
                </div>

                <!-- Message content -->
                <div class="flex flex-col gap-2 max-w-[80%]">
                    <div
                        :class="[
                            'px-4 py-2.5 rounded-2xl',
                            message.sender === user ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-100'
                        ]"
                    >
                        {{ message.message }}
                    </div>

                    <!-- Message actions -->
                    <!-- <div class="flex gap-2">
                        <button
                            class="p-1 text-gray-500 hover:text-gray-400 transition-colors"
                            @click="copyMessage(message.content)"
                        >
                            <Copy class="w-4 h-4" />
                        </button>
                        <button class="p-1 text-gray-500 hover:text-gray-400 transition-colors">
                            <RefreshCw class="w-4 h-4" />
                        </button>
                        <button class="p-1 text-gray-500 hover:text-gray-400 transition-colors">
                            <Volume2 class="w-4 h-4" />
                        </button>
                    </div> -->
                </div>
            </div>

            <!-- Typing indicator -->
            <!-- <div
                v-if="isStreaming"
                :key="'typing'"
                class="flex gap-3"
            >
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center">
                        <Bot class="w-5 h-5 text-blue-400" />
                    </div>
                </div>
                <div class="flex flex-col gap-2 max-w-[80%]">
                    <div class="px-4 py-2.5 rounded-2xl bg-gray-800 text-gray-100">
                    <span class="inline-flex gap-1">
                        <span class="animate-bounce">.</span>
                        <span class="animate-bounce" style="animation-delay: 0.2s">.</span>
                        <span class="animate-bounce" style="animation-delay: 0.4s">.</span>
                    </span>
                    </div>
                </div>
            </div> -->
        </TransitionGroup>
    </div>

    <!-- Input area -->
    <div class="p-4 bg-gray-900 border-t border-gray-800">
        <form @submit.prevent="sendMessage" class="flex gap-3">
            <!-- <button
                type="button"
                class="p-2 text-gray-400 hover:text-gray-300 transition-colors"
            >
                <Paperclip class="w-5 h-5" />
            </button> -->
            <input
                ref="messageInput"
                v-model="newMessage"
                type="text"
                placeholder="Type your message here..."
                class="flex-1 px-4 py-2 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 outline-none focus:ring-2 focus:ring-gray-700 placeholder-gray-500"
                :disabled="isSending"
            />
            <!-- <button
                type="button"
                class="p-2 text-gray-400 hover:text-gray-300 transition-colors"
            >
                <Mic class="w-5 h-5" />
            </button> -->
            <button
                type="submit"
                class="px-3 bg-gray-800 hover:bg-gray-700 text-gray-100 transition-colors outline-none rounded-full focus:ring-2 focus:ring-gray-700 active:bg-gray-900"
                :disabled="isSending"
            >
                <SendIcon class="w-5 h-5" />
            </button>
        </form>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Typing animation */
.animate-bounce {
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
</style>
