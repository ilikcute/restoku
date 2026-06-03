module.exports = {
  apps: [
    {
      name: 'restoku-reverb',
      script: 'artisan',
      interpreter: 'php',
      args: 'reverb:start',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'production'
      }
    },
    {
      name: 'restoku-queue',
      script: 'artisan',
      interpreter: 'php',
      args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '300M'
    }
  ]
};
