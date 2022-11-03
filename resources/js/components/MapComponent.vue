<template>
    <div id="map">
        <div class="map-layers">
            <div class="flex-row"><input type="checkbox" v-model="inj_all" @change="turnLayer($event,'inj_all')"><span> INJ</span></div>
            <div style="padding-left:10px;">
                <div class="flex-row"><input type="checkbox" v-model="inj_a1" @change="turnLayer($event,'inj_a1')"><span> A1</span></div>
                <div class="flex-row"><input type="checkbox" v-model="inj_a2" @change="turnLayer($event,'inj_a2')"><span> A2</span></div>
                <div class="flex-row"><input type="checkbox" v-model="inj_a3" @change="turnLayer($event,'inj_a3')"><span> A3</span></div>
            </div>
            <div class="flex-row"><input type="checkbox" v-model="prod_all" @change="turnLayer($event,'prod_all')"><span> PROD</span></div>
            <div style="padding-left:10px;">
                <div class="flex-row"><input type="checkbox" v-model="prod_a1" @change="turnLayer($event,'prod_a1')"><span> A1</span></div>
                <div class="flex-row"><input type="checkbox" v-model="prod_a2" @change="turnLayer($event,'prod_a2')"><span> A2</span></div>
                <div class="flex-row"><input type="checkbox" v-model="prod_a3" @change="turnLayer($event,'prod_a3')"><span> A3</span></div>
            </div>
        </div>
        <measure-component :map="map"></measure-component>
    </div>
</template>
<style>
    .map-layers {
        width: 200px;
        height: 400px;
        background-color: white;
        position: absolute;
        top: 70px;
        left: 10px;
        z-index: 1;
        overflow: auto;
    }
</style>
<script>
    import { Map, View } from 'ol';
    import { Tile as TileLayer } from 'ol/layer';
    import { OSM } from 'ol/source';
    import TileWMS from 'ol/source/TileWMS';
    //import proj4 from 'proj4';
    import {fromLonLat} from 'ol/proj';
    import {ScaleLine, defaults as defaultControls} from 'ol/control';
    import 'ol/ol.css';
    export default {
        name: 'Map',
        data() { 
            return {
              inj_all:true,
              inj_a1:true,
              inj_a2:true,
              inj_a3:true,
              prod_all:true, 
              prod_a1:true, 
              prod_a2:true, 
              prod_a3:true, 
              map: null,
              wellLayer:null,
            } 
        },
        mounted() {
            const scaleControl = new ScaleLine({
                units: 'metric',
                bar: true,
                steps: 8,
                text: true,
                minWidth: 140,
            });
            this.wellLayer = new TileLayer({
                        visible: true,
                        source: new TileWMS({
                        url: 'http://localhost:8081/geoserver/cite/wms',
                        params: {'FORMAT': 'image/png', 
                                'VERSION': '1.1.1',
                                tiled: true,
                                "STYLES": '',
                                "LAYERS": 'cite:wells',
                                "exceptions": 'application/vnd.ogc.se_inimage',
                            tilesOrigin: 6367290.411185859 + "," + 5619551.815258048
                        }})});
            this.map = new Map({
                target: 'map',
                controls: defaultControls().extend([scaleControl]),
                layers: [
                    new TileLayer({
                        source: new OSM()
                    }),
                    this.wellLayer
                ],
                view: new View({
                    center: fromLonLat([65.877237,48.835187]),
                    zoom: 5
                })
            })
        },
        methods: {
            turnLayer(e,wellType) {
                if(wellType.indexOf('inj')>-1){
                    if(wellType == 'inj_all'){
                        this.inj_all = e.target.checked;
                        this.inj_a1 = e.target.checked
                        this.inj_a2 = e.target.checked
                        this.inj_a3 = e.target.checked
                    }else{
                        this[wellType] = e.target.checked;
                        this.inj_all = this.inj_a1||this.inj_a2||this.inj_a3;
                    }
                }else{
                    if(wellType == 'prod_all'){
                        this.prod_all = e.target.checked;
                        this.prod_a1 = e.target.checked
                        this.prod_a2 = e.target.checked
                        this.prod_a3 = e.target.checked
                    }else{
                        this[wellType] = e.target.checked;
                        this.prod_all = this.prod_a1||this.prod_a2||this.prod_a3;
                    }
                }

                let query = '1=1';
                if(!this.inj_all&&!this.prod_all){
                    query = " well_type not in ('inj','prod')";
                }else{
                    let queryArr = [];
                    if(this.inj_a1){
                        queryArr.push(`(horizont='A-1' and well_type='inj')`);
                    }
                    if(this.inj_a2){
                        queryArr.push(`(horizont='A-2' and well_type='inj')`);
                    }
                    if(this.inj_a3){
                        queryArr.push(`(horizont='A-3' and well_type='inj')`);
                    }
                    if(this.prod_a1){
                        queryArr.push(`(horizont='A-1' and well_type='prod')`);
                    }
                    if(this.prod_a2){
                        queryArr.push(`(horizont='A-2' and well_type='prod')`);
                    }
                    if(this.prod_a3){
                        queryArr.push(`(horizont='A-3' and well_type='prod')`);
                    }
                    if(queryArr.length>0){
                        query = queryArr.join(' or ');
                    }
                }
                
                this.wellLayer.getSource().updateParams({'CQL_FILTER':query});
                this.map.updateSize();
            },
            handleInput() {
            },
        },
    }
</script>
