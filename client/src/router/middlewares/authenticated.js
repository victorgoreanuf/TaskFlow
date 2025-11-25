export default async function authenticated({next, store}) {

    // If the authentication check has not completed yet (on page refresh)
    if (!store.getters["auth/checked"]) {
        // CRITICAL FIX: Dispatch the async action and WAIT for it.
        // The 'auth/user' action will set 'auth/checked' to true in its finally block.
        await store.dispatch("auth/user");
    }

    // Now the state is checked. If the user is not authenticated, redirect.
    if (!store.getters["auth/authenticated"]) {
        // Use 'return next()' to correctly stop the middleware chain and redirect.
        return next({name: "login"});
    }

    // If all checks pass and the user is authenticated, proceed.
    return next();
}