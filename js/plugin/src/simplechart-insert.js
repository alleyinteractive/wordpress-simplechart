import SimplechartController from './simplechart-controller';
import SimplechartItem from './simplechart-item';
import SimplechartPostFrame from './simplechart-post-frame';
import SimplechartToolbar from './simplechart-toolbar';
import SimplechartView from './simplechart-view';

document.addEventListener('DOMContentLoaded', () => {
  wp.media.controller.Simplechart = SimplechartController;
  wp.media.view.Toolbar.Simplechart = SimplechartToolbar;
  wp.media.view.SimplechartItem = SimplechartItem;
  wp.media.view.Simplechart = SimplechartView;
  wp.media.view.MediaFrame.Post = SimplechartPostFrame;
});
