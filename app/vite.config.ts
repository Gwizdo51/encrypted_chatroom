// import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        host: true, // exposes vite dev server to the network
        port: 5173,
        strictPort: true, // vite should fail if the 5173 port is not available
        cors: {
            // origin: 'http://100.100.151.68', // the IP address of the server
            origin: 'http://100.124.238.99:8000', // the IP address of the server
            // origin: 'https://yt2mp3.100.100.151.68.nip.io:5000', // the IP address of the server
            credentials: true,
        },
        hmr: {
            // host: '100.100.151.68' // the host name of the server
            host: '100.124.238.99', // the host name of the server
        },
        // https: {
        //     cert: fs.readFileSync(path.resolve(__dirname, '/mnt/self.crt')),
        //     key: fs.readFileSync(path.resolve(__dirname, '/mnt/self.key')),
        // }
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        // wayfinder({
        //     formVariants: true,
        // }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ]
});
