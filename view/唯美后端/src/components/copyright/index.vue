<template>
    <GlobalFooter class="i-copyright" :links="links" :copyright="copyright" />
</template>
<script>
    export default {
        name: 'i-copyright',
        data () {
            return {
                links: [
                    {
                        title: '',
                        key: '',
                        href: '',
                        blankTarget: false
                    }
                ],
                copyright: 'Copyright © 2020 一诺网络科技有限公司'
            }
        },
        mounted () {
            this.getVersion();
        },
        methods: {
            getVersion () {
                this.$store.dispatch('admin/db/get', {
                    dbName: 'sys',
                    path: 'user.info',
                    user: true
                }).then(res => {
                    this.copyright += res.version ? '  |  ' + res.version : '';
                })
            }
        }
    }
</script>
<style lang="less">
    .i-copyright{
        flex: 0 0 auto;
    }
</style>
