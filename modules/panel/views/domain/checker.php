<?php
/** @var yii\web\View $this */

$this->title = 'Чекер дропов';
$this->params['breadcrumbs'][] = $this->title;
?>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>


<div id="app">
    <div class="row">
        <div class="col">
            <ul class="list-group">
                <li class="list-group-item" v-for="(domain, index) in domains" :key="index" :class="{ 'list-group-item-danger': domainHighlighted.includes(index) }">
                    <button @click="removeDomain(index)" class="btn btn-danger btn-sm" style="font-size: 13px;">удалить</button>
                    <img :src="'https://webmaster.yandex.ru/sqicounter?theme=light&amp;host=' + domain" style="width:60px; margin-left: 2px">
                    <a style="margin-left: 2px" :href="'https://a.pr-cy.ru/' + domain + '/'" target="_blank">
                        <img :src="'//s.pr-cy.ru/counters/' + domain" alt="Анализ сайта - PR-CY Rank" style="width:60px;">
                    </a>
                    <input  type="text" class="form-control" :value="domain" style="margin-left: 2px;     height: 30px; display: inline-block; max-width: 200px;">
                    <a :href="'https://www.nic.ru/whois/?searchWord=' + domain" style="font-size: 13px; margin-left: 2px" target="_blank">wh</a>
                    <a  @click="copyDomainToClipboard(index, true)" :href="'https://www.google.com/search?q=' + domain.replace('.', ' ')" style="font-size: 13px; margin-left: 2px" class="btn btn-dark btn-sm" target="_blank">без точки</a>
                    <a  @click="copyDomainToClipboard(index, true)" :href="'https://www.google.com/search?q==site:' + domain" style="font-size: 13px; margin-left: 2px" class="btn btn-dark btn-sm" target="_blank">google</a>
                    <button @click="copyDomainToClipboard(index)" class="btn btn-primary btn-sm" style="margin-left: 2px; font-size: 13px;">copy</button>
                </li>
            </ul>
        </div>
        <div class="col">
            <div class="form-group">
                <label for="textareaDomains">Domains</label>
                <textarea class="form-control" id="textareaDomains" rows="16" v-model="message"
                          @input="updateDomains"></textarea>
            </div>
            <div class="form-group" v-for="(chunk, chunkIndex) in chunkedDomains" :key="chunkIndex">
                <input class="form-control" :id="'inputDomains' + chunkIndex" :value="generateGoogleSearchQuery(chunk)" readonly>
                <a :href="'https://www.google.com/search?q=' + generateGoogleSearchQuery(chunk)" class="btn"
                   :class="{ 'btn-danger': buttonHighlighted.includes(chunkIndex), 'btn-primary': !buttonHighlighted.includes(chunkIndex) }" @click="buttonHighlight(chunkIndex)"  target="_blank">Google</a>
            </div>
        </div>
    </div>
</div>

<script>
    const {createApp} = Vue

    createApp({
        data() {
            return {
                message: '',
                domains: [],
                domainHighlighted: [],
                buttonHighlighted: []
            }
        },
        methods: {
            updateDomains() {
                this.domains = this.message.split('\n').filter(domain => domain.trim() !== '');
            },
            removeDomain(index) {
                this.domains.splice(index, 1);
                this.updateTextarea();
                this.domainHighlighted = this.domainHighlighted.filter(idx => idx !== index);
            },
            updateTextarea() {
                this.message = this.domains.join('\n');
            },
            copyDomainToClipboard(index, light = false) {
                const domain = this.domains[index];
                const dummyInput = document.createElement('input');
                document.body.appendChild(dummyInput);
                dummyInput.setAttribute('value', domain);
                dummyInput.select();
                document.execCommand('copy');
                document.body.removeChild(dummyInput);
                if(light) {
                    this.domainHighlighted.push(index);
                }
            },
            generateGoogleSearchQuery(chunk) {
                return 'site:' + chunk.join(' OR site:');
            },
            chunkArray(array, size) {
                const result = [];
                for (let i = 0; i < array.length; i += size) {
                    result.push(array.slice(i, i + size));
                }
                return result;
            },
            buttonHighlight(chunkIndex) {
                this.buttonHighlighted.push(chunkIndex);
            }
        },
        computed: {
            chunkedDomains() {
                return this.chunkArray(this.domains, 20);
            }
        }
    }).mount('#app')
</script>