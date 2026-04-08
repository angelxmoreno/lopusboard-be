#!/usr/bin/env bun

type Options = {
  url: string;
  jwt: string;
};

function printHelp(): void {
  console.log(`Usage:
  bun tests/client-test.ts --url http://127.0.0.1:8765/api/identity/me --jwt <token>

Or set:
  IDENTITY_URL
  IDENTITY_JWT`);
}

function parseArgs(argv: string[]): Options {
  if (argv.includes("--help") || argv.includes("-h")) {
    printHelp();
    process.exit(0);
  }

  const args = new Map<string, string>();

  for (let index = 0; index < argv.length; index += 1) {
    const current = argv[index];
    if (!current.startsWith("--")) {
      continue;
    }

    const key = current.slice(2);
    const value = argv[index + 1];
    if (!value || value.startsWith("--")) {
      throw new Error(`Missing value for --${key}`);
    }

    args.set(key, value);
    index += 1;
  }

  const url = args.get("url") ?? Bun.env.IDENTITY_URL;
  const jwt = args.get("jwt") ?? Bun.env.IDENTITY_JWT;

  if (!url) {
    throw new Error(
      "Missing identity URL. Pass --url <url> or set IDENTITY_URL.",
    );
  }

  if (!jwt) {
    throw new Error(
      "Missing JWT. Pass --jwt <jwt> or set IDENTITY_JWT.",
    );
  }

  return { url, jwt };
}

function assert(condition: unknown, message: string): asserts condition {
  if (!condition) {
    throw new Error(message);
  }
}

async function main(): Promise<void> {
  const options = parseArgs(process.argv.slice(2));

  const response = await fetch(options.url, {
    headers: {
      Authorization: `Bearer ${options.jwt}`,
      Accept: "application/json",
    },
  });

  const bodyText = await response.text();

  assert(
    response.ok,
    `Identity endpoint failed with ${response.status} ${response.statusText}\n${bodyText}`,
  );

  const body = JSON.parse(bodyText) as {
    data?: {
      remoteIdentity?: {
        provider?: string;
        providerUserId?: string;
      };
      user?: {
        id?: number | string | null;
        email?: string | null;
      };
    };
  };

  assert(body.data, 'Expected response body to contain a "data" object.');
  assert(
    body.data.remoteIdentity,
    'Expected response body to contain "data.remoteIdentity".',
  );
  assert(
    body.data.user,
    'Expected response body to contain "data.user".',
  );
  assert(
    typeof body.data.remoteIdentity.provider === "string" &&
      body.data.remoteIdentity.provider.length > 0,
    'Expected "data.remoteIdentity.provider" to be a non-empty string.',
  );
  assert(
    typeof body.data.remoteIdentity.providerUserId === "string" &&
      body.data.remoteIdentity.providerUserId.length > 0,
    'Expected "data.remoteIdentity.providerUserId" to be a non-empty string.',
  );

  console.log("Identity endpoint OK");
  console.log(JSON.stringify(body, null, 2));
}

await main();
