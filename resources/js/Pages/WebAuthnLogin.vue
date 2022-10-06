<script setup>
import {onMounted, toRaw} from 'vue';

let props = defineProps(['publicKey']);
const arrayToBase64String = (a) => btoa(String.fromCharCode(...a));

// Decodes a Base64Url string
const base64UrlDecode = (input) => {
  input = input
      .replace(/-/g, '+')
      .replace(/_/g, '/');

  const pad = input.length % 4;
  if (pad) {
    if (pad === 1) {
      throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
    }
    input += new Array(5-pad).join('=');
  }

  return window.atob(input);
};

const authWithKey = (publicKeyCredentialCreationOptions) => {
    const challenge = Uint8Array.from(base64UrlDecode(publicKeyCredentialCreationOptions.challenge), c => c.charCodeAt(0));
    const allowCredentials = publicKeyCredentialCreationOptions.allowCredentials.map(
        (item) => {
            return {
                ...item,
                id: Uint8Array.from(base64UrlDecode(item.id), c => c.charCodeAt(0))
            }
        }
    );

    navigator.credentials.get({
        publicKey: {
            ...publicKeyCredentialCreationOptions,
            challenge: challenge,
            allowCredentials: allowCredentials,
        }
    })
    .then(credential => {
        const utf8Decoder = new TextDecoder('utf-8');
        return {
            authenticatorAttachment: credential.authenticatorAttachment,
            id: credential.id,
            rawId: arrayToBase64String(new Uint8Array(credential.rawId)),
            type: credential.type,
            response: {
                clientDataJSON: credential.response.clientDataJSON ?
                    arrayToBase64String(new Uint8Array(credential.response.clientDataJSON)) : null,
                authenticatorData: credential.response.authenticatorData ?
                    arrayToBase64String(new Uint8Array(credential.response.authenticatorData)) : null,
                signature: credential.response.signature ?
                    arrayToBase64String(new Uint8Array(credential.response.signature)) : null,
                userHandle: credential.response.userHandle ?
                    arrayToBase64String(new Uint8Array(credential.response.userHandle)) : null,
            },
        }
    })
    .then(response => {
        console.log('sadfasdf', response);
       return axios
            .post('/webauthn/auth', response);
    })
    .then(response => {
        console.log('last response', response);
        window.location.href = '/dashboard';
    })
    .catch(console.error);
}

onMounted(() => {
    let x = toRaw(props.publicKey);

    authWithKey(x);
});

</script>

<template>
    <section class="flex justify-center items-center h-full">
        <div class="w-1/2 h-1/2 p-4 bg-gray-200 border border-gray-200 shadow-md rounded">
            <h2 class="text-gray-600 text-xl text-center font-bold">
                WebAuthn Login
            </h2>
            <a class="text-black hover:underline" href="/login">Login With password</a>
        </div>
    </section>
</template>
