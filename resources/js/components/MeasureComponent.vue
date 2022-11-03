<template>
    <div class="flex-row measure-tools">
        <button type="button" @click="activateTool('area')">Area</button>
        <button type="button" @click="activateTool('length')">Length</button>
    </div>
</template>
<style>
 .measure-tools{
     padding:5px;
     width: 200px;
    height: 50px;
    position: absolute;
    top: 10px;
    left: 100px;
    z-index: 1;
 }
</style>
<script>
import Draw from 'ol/interaction/Draw';
import Overlay from 'ol/Overlay';
import {Circle as CircleStyle, Fill, Stroke, Style} from 'ol/style';
import {LineString, Polygon} from 'ol/geom';
import { Vector as VectorSource} from 'ol/source';
import {Tile as TileLayer, Vector as VectorLayer} from 'ol/layer';
import {getArea, getLength} from 'ol/sphere';
import {unByKey} from 'ol/Observable';
export default {
    props: ['map'],
    data(){
        return {
            vectorLayer:null,
            sketch:null,
            helpTooltipElement:null,
            helpTooltip:null,
            measureTooltipElement:null,
            measureTooltip:null,
            continuePolygonMsg :'Click to continue drawing the polygon',
            continueLineMsg : 'Click to continue drawing the line',
            draw:null,
            listener:null
        }
    },
    mounted() {
        window.that = this;
    },
    methods:{
        init(){
            const source = new VectorSource();

            this.vectorLayer = new VectorLayer({
                source: source,
                style: {
                    'fill-color': 'rgba(255, 255, 255, 0.2)',
                    'stroke-color': '#ffcc33',
                    'stroke-width': 2,
                    'circle-radius': 7,
                    'circle-fill-color': '#ffcc33',
                },
            });
            this.map.addLayer(this.vectorLayer);
            this.createMeasureTooltip();
            this.createHelpTooltip();
            this.map.on('pointermove', this.pointerMoveHandler);
            this.map.getViewport().addEventListener('mouseout', function () {
                window.that.helpTooltipElement.classList.add('hidden');
            });
            window.getArea = getArea;
            window.getLength = getLength;
        },
        activateTool(toolType){
            if(!this.vectorLayer){
                this.init();   
            }
            if(this.draw){
                this.map.removeInteraction(this.draw);
            }
            this.createInteraction(toolType);
            this.map.addInteraction(this.draw);
        },
        pointerMoveHandler (evt) {
            if (evt.dragging) {
                return;
            }
            /** @type {string} */
            let helpMsg = 'Click to start drawing';

            if (window.that.sketch) {
                const geom = window.that.sketch.getGeometry();
                if (geom instanceof Polygon) {
                    helpMsg = this.continuePolygonMsg;
                } else if (geom instanceof LineString) {
                    helpMsg = this.continueLineMsg;
                }
            }
            window.that.helpTooltipElement.innerHTML = helpMsg;
            window.that.helpTooltip.setPosition(evt.coordinate);
            window.that.helpTooltipElement.classList.remove('hidden');
        },
        formatLength(line) {
            const length = window.getLength(line);
            let output;
            if (length > 100) {
                output = Math.round((length / 1000) * 100) / 100 + ' ' + 'km';
            } else {
                output = Math.round(length * 100) / 100 + ' ' + 'm';
            }
            return output;
        },
        formatArea(polygon) {
            const area = window.getArea(polygon);
            let output;
            if (area > 10000) {
                output = Math.round((area / 1000000) * 100) / 100 + ' ' + 'km<sup>2</sup>';
            } else {
                output = Math.round(area * 100) / 100 + ' ' + 'm<sup>2</sup>';
            }
            return output;
        },
        createHelpTooltip() {
            if (this.helpTooltipElement) {
                this.helpTooltipElement.parentNode.removeChild(this.helpTooltipElement);
            }
            this.helpTooltipElement = document.createElement('div');
            this.helpTooltipElement.className = 'ol-tooltip hidden';
            this.helpTooltip = new Overlay({
                element: this.helpTooltipElement,
                offset: [15, 0],
                positioning: 'center-left',
            });
            this.map.addOverlay(this.helpTooltip);
        },
        createMeasureTooltip() {
            if (this.measureTooltipElement) {
                this.measureTooltipElement.parentNode.removeChild(this.measureTooltipElement);
            }
            this.measureTooltipElement = document.createElement('div');
            this.measureTooltipElement.className = 'ol-tooltip ol-tooltip-measure';
            this.measureTooltip = new Overlay({
                element: this.measureTooltipElement,
                offset: [0, -15],
                positioning: 'bottom-center',
                stopEvent: false,
                insertFirst: false,
            });
            this.map.addOverlay(this.measureTooltip);
        },
        createInteraction(typeSelect) {
            const type = typeSelect == 'area' ? 'Polygon' : 'LineString';
            this.draw = new Draw({
                source: this.vectorLayer.getSource(),
                type: type,
                style: new Style({
                fill: new Fill({
                    color: 'rgba(255, 255, 255, 0.2)',
                }),
                stroke: new Stroke({
                    color: 'rgba(0, 0, 0, 0.5)',
                    lineDash: [10, 10],
                    width: 2,
                }),
                image: new CircleStyle({
                    radius: 5,
                    stroke: new Stroke({
                    color: 'rgba(0, 0, 0, 0.7)',
                    }),
                    fill: new Fill({
                    color: 'rgba(255, 255, 255, 0.2)',
                    }),
                }),
                }),
            });
            this.draw.on('drawstart', function (evt) {
                // set sketch
                window.that.sketch = evt.feature;

                /** @type {import("../src/ol/coordinate.js").Coordinate|undefined} */
                let tooltipCoord = evt.coordinate;

                window.that.listener = window.that.sketch.getGeometry().on('change', function (evt) {
                    const geom = evt.target;
                    let output;
                    if (geom instanceof Polygon) {
                        output = window.that.formatArea(geom);
                        tooltipCoord = geom.getInteriorPoint().getCoordinates();
                    } else if (geom instanceof LineString) {
                        output = window.that.formatLength(geom);
                        tooltipCoord = geom.getLastCoordinate();
                    }
                    window.that.measureTooltipElement.innerHTML = output;
                    window.that.measureTooltip.setPosition(tooltipCoord);
                });
            });

            this.draw.on('drawend', function () {
                window.that.measureTooltipElement.className = 'ol-tooltip ol-tooltip-static';
                window.that.measureTooltip.setOffset([0, -7]);
                // unset sketch
                window.that.sketch = null;
                // unset tooltip so that a new one can be created
                window.that.measureTooltipElement = null;
                window.that.createMeasureTooltip();
                unByKey(window.that.listener);
            });
        }
    }    
}
</script>