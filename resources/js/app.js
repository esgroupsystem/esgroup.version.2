import './bootstrap';

import SimpleBar from 'simplebar';
import Choices from 'choices.js';
import flatpickr from 'flatpickr';
import Dropzone from 'dropzone';
import Swiper from 'swiper';
import Chart from 'chart.js/auto';
import L from 'leaflet';
import dayjs from 'dayjs';

window.SimpleBar = SimpleBar;
window.Choices = Choices;
window.flatpickr = flatpickr;
window.Dropzone = Dropzone;
window.Swiper = Swiper;
window.Chart = Chart;
window.L = L;
window.dayjs = dayjs;

if (window.Dropzone) {
    window.Dropzone.autoDiscover = false;
}
