import { startStimulusApp } from '@symfony/stimulus-bundle';
import WishlistController from './controllers/wishlist_controller.js';
import HorizontalScrollController from './controllers/horizontal_scroll_controller.js';

const app = startStimulusApp();
app.register('wishlist', WishlistController);
app.register('horizontal-scroll', HorizontalScrollController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
