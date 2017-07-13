/**
 * DEPRICATED : use Roo.XComponents now..
 * 
 * Pman.register({
 modKey : '00-admin-xxxx',
 module : Pman.Tab.projectMgr, << really a components..
 part : [ 'Admin', 'ProjectManager' ]
 moduleOwner : 
 region : 'center',
 parent : Pman.layout
 })
 * 
 */
Pman.register = function (obj)
{

    //this.xregister(obj);


    // old style calls go in here..
    // we need to convert the object so that it looks a bit like an XCompoenent..

    obj.render = function ()
    {
        if (!this.parent) {
            Roo.log("Skip module, as parent does not exist");
            Roo.log(this);
            return;
        }
        //if (typeof(mod) == 'function') {
        //    mod();

        if (typeof (this.region) == 'undefined') {
            Roo.log("Module does not have region defined, skipping");
            Roo.log(this);
            return;
        }
        if (this.module.disabled) {
            Roo.log("Module disabled, should not rendering");
            Roo.log(this);
            return;
        }

        if (!this.parent.layout) {
            Roo.log("Module parent does not have property layout.");
            Roo.log(this);
            return;
        }

        // honour DEPRICATED permname setings..
        // new code should use PART name, and matching permissions.
        if (this.permname && this.permname.length) {
            if (!Pman.hasPerm(this.permname, 'S')) {
                return;
            }

        }
        this.add(this.parent.layout, this.region);
        this.el = this.layout;



    };
    // map some of the standard properties..
    obj.order = obj.modKey;

    // a bit risky...



    // the other issue we have is that


    // Roo.log("CALLING XComponent register with : " + obj.name);
    Roo.log(obj);
    // this will call xregister as it's the on.register handler..
    Roo.XComponent.register(obj.isTop ? obj : Roo.apply(obj.module, obj));

}