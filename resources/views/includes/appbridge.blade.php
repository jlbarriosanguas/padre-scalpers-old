<script src="https://unpkg.com/@shopify/app-bridge"></script>
<script>
var AppBridge = window['app-bridge'];
var actions = window['app-bridge'].actions;
var createApp = AppBridge.default;
var TitleBar = actions.TitleBar;
var Button = actions.Button;
var ButtonGroup = actions.ButtonGroup;
var Redirect = actions.Redirect;

var url = new URL(window.location);
var shopOrigin = url.searchParams.get("shop");

var app = createApp({
    apiKey: "{{env('PADRE_API_KEY')}}",
    shopOrigin: shopOrigin,
});

const feedsButton = Button.create(app, { label: 'Feeds' });
const collectionsButton = Button.create(app, { label: 'Colecciones' });
const offerButton = Button.create(app, { label: 'Ofertas' });
const applicantsButton = Button.create(app, { label: 'Candidatos' });
const newOfferButton = Button.create(app, {label: 'Nueva Oferta'});
const rrhhGroupButton = ButtonGroup.create(app, {label: 'RRHH', buttons: [offerButton, applicantsButton]});
const importerGroupButton = ButtonGroup.create(app, {label: 'Scalpify', buttons: [collectionsButton]});
const redirect = Redirect.create(app);

feedsButton.subscribe('click', () => {
    redirect.dispatch(Redirect.Action.APP, '/feeds/view');
});

collectionsButton.subscribe('click', () => {
    redirect.dispatch(Redirect.Action.APP, '/scalpify/collections/view');
});

offerButton.subscribe('click', () => {
    redirect.dispatch(Redirect.Action.APP, '/rrhh/view');
});

applicantsButton.subscribe('click', () => {
    redirect.dispatch(Redirect.Action.APP, '/rrhh/applicants');
});

newOfferButton.subscribe('click', () => {
    redirect.dispatch(Redirect.Action.APP, '/rrhh/create-offer');
});

</script>